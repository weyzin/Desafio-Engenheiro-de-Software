<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\StoreRequest;
use App\Http\Requests\Users\UpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        // IMPORTANTE: o model User NÃO deve ter GlobalScope de tenant.
        $q = User::query();

        if ($s = trim((string) $request->input('q'))) {
            $q->where(function($w) use ($s) {
                $w->where('name','like',"%$s%")->orWhere('email','like',"%$s%");
            });
        }
        if ($tid = $request->input('tenant_id')) {
            $q->where('tenant_id', $tid);
        }
        $q->orderBy('created_at','desc');

        $per = (int) $request->input('per_page', 20);
        $pag = $q->paginate($per)->appends($request->query());

        // não vazar hash
        $items = collect($pag->items())->map(function(User $u){
            return [
                'id'        => $u->id,
                'tenant_id' => $u->tenant_id,
                'name'      => $u->name,
                'email'     => $u->email,
                'role'      => $u->role,
                'created_at'=> $u->created_at,
                'updated_at'=> $u->updated_at,
            ];
        });

        return response()->json([
            'data'  => $items,
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
        $this->authorize('create', User::class);

        $data = $request->validated();
        $u = new User();
        $u->tenant_id = $data['tenant_id'] ?? null;
        $u->name      = $data['name'];
        $u->email     = $data['email'];
        $u->role      = $data['role'];
        $u->password  = Hash::make($data['password']);
        $u->save();

        return response()->json(['data' => [
            'id'=>$u->id,'tenant_id'=>$u->tenant_id,'name'=>$u->name,'email'=>$u->email,'role'=>$u->role,
            'created_at'=>$u->created_at,'updated_at'=>$u->updated_at,
        ]], 201);
    }

    public function show(int $id)
    {
        $u = User::findOrFail($id);
        $this->authorize('view', $u);
        return response()->json(['data' => [
            'id'=>$u->id,'tenant_id'=>$u->tenant_id,'name'=>$u->name,'email'=>$u->email,'role'=>$u->role,
            'created_at'=>$u->created_at,'updated_at'=>$u->updated_at,
        ]]);
    }

    public function update(UpdateRequest $request, int $id)
    {
        $u = User::findOrFail($id);
        $this->authorize('update', $u);

        $data = $request->validated();
        $u->tenant_id = ($data['role'] === 'superuser') ? null : ($data['tenant_id'] ?? $u->tenant_id);
        $u->name      = $data['name'];
        $u->email     = $data['email'];
        $u->role      = $data['role'];
        if (!empty($data['password'])) {
            $u->password = Hash::make($data['password']);
        }
        $u->save();

        return response()->json(['data' => [
            'id'=>$u->id,'tenant_id'=>$u->tenant_id,'name'=>$u->name,'email'=>$u->email,'role'=>$u->role,
            'created_at'=>$u->created_at,'updated_at'=>$u->updated_at,
        ]]);
    }

    public function destroy(int $id)
    {
        $u = User::findOrFail($id);
        $this->authorize('delete', $u);
        $u->delete();
        return response()->noContent();
    }
}
