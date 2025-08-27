<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tenants\StoreRequest;
use App\Http\Requests\Tenants\UpdateRequest;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Tenant::class);

        $q = Tenant::query();
        if ($s = trim((string) $request->input('q'))) {
            $q->where(function($w) use ($s) {
                $w->where('name','like',"%$s%")->orWhere('slug','like',"$s%");
            });
        }
        $q->orderBy('created_at','desc');

        $per = (int) $request->input('per_page', 20);
        $pag = $q->paginate($per)->appends($request->query());

        return response()->json([
            'data'  => $pag->items(),
            'meta'  => [
                'total'     => $pag->total(),
                'page'      => $pag->currentPage(),
                'per_page'  => $pag->perPage(),
                'last_page' => $pag->lastPage(),
                'from'      => $pag->firstItem(),
                'to'        => $pag->lastItem(),
                'links'     => $pag->linkCollection(),
            ],
            'links' => ['next'=>$pag->nextPageUrl(),'prev'=>$pag->previousPageUrl()],
        ]);
    }

    public function store(StoreRequest $request)
    {
        $this->authorize('create', Tenant::class);
        $t = Tenant::create($request->validated());
        return response()->json(['data' => $t], 201);
    }

    public function show(string $id)
    {
        $t = Tenant::findOrFail($id);
        $this->authorize('view', $t);
        return response()->json(['data' => $t]);
    }

    public function update(UpdateRequest $request, string $id)
    {
        $t = Tenant::findOrFail($id);
        $this->authorize('update', $t);
        $t->fill($request->validated())->save();
        return response()->json(['data' => $t]);
    }

    public function destroy(string $id)
    {
        $t = Tenant::findOrFail($id);
        $this->authorize('delete', $t);

        $hasUsers    = User::where('tenant_id', $id)->exists();
        $hasVehicles = Vehicle::where('tenant_id', $id)->exists();

        if ($hasUsers || $hasVehicles) {
            return response()->json([
                'code'    => 'TENANT_NOT_EMPTY',
                'message' => 'Tenant possui usuários ou veículos vinculados.',
            ], 409);
        }

        $t->delete();
        return response()->noContent();
    }
}
