<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vehicles\IndexRequest;
use App\Http\Requests\Vehicles\StoreRequest;
use App\Http\Requests\Vehicles\UpdateRequest;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Support\Query\SortParser;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

class VehicleController extends Controller
{
    public function index(IndexRequest $request)
    {
        $this->authorize('viewAny', Vehicle::class);

        $q = Vehicle::query();

        if ($b = $request->input('brand')) {
            $q->where('brand', 'like', $b.'%');
        }
        if ($m = $request->input('model')) {
            $q->where('model', 'like', $m.'%');
        }
        if (($min = $request->input('price_min')) !== null) {
            $q->where('price', '>=', $min);
        }
        if (($max = $request->input('price_max')) !== null) {
            $q->where('price', '<=', $max);
        }

        // Ordenação
        $allowedSorts = ['price','year','created_at'];
        foreach (SortParser::parse($request->input('sort'), $allowedSorts) as [$col,$dir]) {
            $q->orderBy($col, $dir);
        }
        if (!$request->filled('sort')) {
            $q->orderByDesc('created_at');
        }

        $perPage = (int) ($request->input('per_page', 20));
        $pag = $q->paginate($perPage)->appends($request->query());

        // Resposta paginada: { data, meta, links }
        return (VehicleResource::collection($pag))
            ->additional([
                'meta'  => [
                    'total'     => $pag->total(),
                    'page'      => $pag->currentPage(),
                    'per_page'  => $pag->perPage(),
                    'last_page' => $pag->lastPage(),
                ],
                'links' => [
                    'next' => $pag->nextPageUrl(),
                    'prev' => $pag->previousPageUrl(),
                ],
            ])->response()->header('Cache-Control', 'public, max-age=60');
    }

    public function store(StoreRequest $request)
    {
        $this->authorize('create', Vehicle::class);

        $user = $request->user();
        $data = $request->validated();
        $data['status'] = $data['status'] ?? 'available';
        $data['images_json'] = $data['images'] ?? [];
        unset($data['images']);

        $veh = new Vehicle($data);
        $veh->created_by = $user->id;
        $veh->save();

        return (new VehicleResource($veh))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $id)
    {
        // Global Scope por tenant garante 404 para cross-tenant
        $veh = Vehicle::findOrFail($id);
        $this->authorize('view', $veh);

        return (new VehicleResource($veh))
            ->response()
            ->header('Cache-Control', 'public, max-age=60');
    }

    public function update(UpdateRequest $request, int $id)
    {
        $veh = Vehicle::findOrFail($id);
        $this->authorize('update', $veh);

        $data = $request->validated();
        if (array_key_exists('images', $data)) {
            $data['images_json'] = $data['images'] ?? [];
            unset($data['images']);
        }
        $veh->fill($data);
        $veh->updated_by = $request->user()->id;
        $veh->save();

        return new VehicleResource($veh);
    }

    public function destroy(Request $request, int $id)
    {
        $veh = Vehicle::findOrFail($id);
        $this->authorize('delete', $veh);

        $veh->deleted_by = $request->user()->id;
        $veh->save();
        $veh->delete(); // soft delete, se habilitado

        return response()->noContent();
    }
}
