<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vehicles\IndexRequest;
use App\Http\Requests\Vehicles\StoreRequest;
use App\Http\Requests\Vehicles\UpdateRequest;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Support\Query\SortParser;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    private function currentTenantId(Request $request): ?string
    {
        return $request->attributes->get('tenant_id') ?? optional($request->user())->tenant_id;
    }

    public function index(IndexRequest $request)
    {
        $this->authorize('viewAny', Vehicle::class);

        $tenantId = $this->currentTenantId($request);

        // superuser sem tenant ativo → retorna vazio (escolha um via X-Tenant)
        if ($request->user()?->role === 'superuser' && !$tenantId) {
        $per = (int) $request->input('per_page', 20);

        return response()
            ->json([
                'data' => [],
                'meta' => [
                    'total' => 0,
                    'page' => 1,
                    'per_page' => $per,
                    'last_page' => 1,
                    'hint' => 'Superuser sem tenant ativo. Envie X-Tenant para listar veículos.',
                ],
                'links' => ['next' => null, 'prev' => null],
            ])
            ->setEncodingOptions(JSON_PRESERVE_ZERO_FRACTION)
            ->header('Cache-Control', 'public, max-age=30')
            ->header('X-Active-Tenant', 'none');
        }

        $q = Vehicle::query();

        // Força tenant da requisição
        if ($tenantId) {
            $q->where('tenant_id', $tenantId);
        }

        if ($b = $request->input('brand'))  $q->where('brand',  'like', $b.'%');
        if ($m = $request->input('model'))  $q->where('model',  'like', $m.'%');
        if (($min = $request->input('price_min')) !== null) $q->where('price', '>=', $min);
        if (($max = $request->input('price_max')) !== null) $q->where('price', '<=', $max);

        $allowedSorts = ['price','year','created_at'];
        foreach (SortParser::parse($request->input('sort'), $allowedSorts) as [$col,$dir]) {
            $q->orderBy($col, $dir);
        }
        if (!$request->filled('sort')) $q->orderByDesc('created_at');

        $perPage = (int) $request->input('per_page', 20);
        $pag = $q->paginate($perPage)->appends($request->query());

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
            ])
            ->response()
            ->setEncodingOptions(JSON_PRESERVE_ZERO_FRACTION)
            ->header('Cache-Control', 'public, max-age=60');
    }

    public function store(StoreRequest $request)
    {
        $this->authorize('create', Vehicle::class);

        $user = $request->user();
        $data = $request->validated();
        $data['status'] = $data['status'] ?? 'available';
        $data['images_json'] = $data['images'] ?? [];
        unset($data['images']);

        // tenant: para superuser, usa o header; para demais, o do usuário
        $tenantId = $request->attributes->get('tenant_id') ?? $user->tenant_id;

        $veh = new Vehicle($data);
        $veh->tenant_id  = $tenantId;
        $veh->created_by = $user->id;
        $veh->save();

        return response()->json(
            ['data' => (new VehicleResource($veh))->toArray($request)],
            201,
            [],
            JSON_PRESERVE_ZERO_FRACTION
        );
    }

    public function show(Request $request, int $id)
    {
        $tenantId = $this->currentTenantId($request);

        $veh = Vehicle::query()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereKey($id)
            ->firstOrFail();

        $this->authorize('view', $veh);

        return (new VehicleResource($veh))
            ->response()
            ->setEncodingOptions(JSON_PRESERVE_ZERO_FRACTION)
            ->header('Cache-Control', 'public, max-age=60');
    }

    public function update(UpdateRequest $request, int $id)
    {
        $tenantId = $this->currentTenantId($request);

        $veh = Vehicle::query()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereKey($id)
            ->firstOrFail();

        $this->authorize('update', $veh);

        $data = $request->validated();
        if (array_key_exists('images', $data)) {
            $data['images_json'] = $data['images'] ?? [];
            unset($data['images']);
        }

        $veh->fill($data);
        $veh->updated_by = $request->user()->id;
        $veh->save();
        $veh->refresh();

        return response()->json(
            ['data' => (new VehicleResource($veh))->toArray($request)],
            200,
            [],
            JSON_PRESERVE_ZERO_FRACTION
        );
    }

    public function destroy(Request $request, int $id)
    {
        $tenantId = $this->currentTenantId($request);

        $veh = Vehicle::query()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereKey($id)
            ->firstOrFail();

        $this->authorize('delete', $veh);

        $veh->deleted_by = $request->user()->id;
        $veh->save();
        $veh->delete();

        return response()->noContent();
    }
}
