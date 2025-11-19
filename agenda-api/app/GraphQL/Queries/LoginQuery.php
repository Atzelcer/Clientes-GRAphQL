<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\SelectFields;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;

class LoginQuery extends Query
{
    protected $attributes = [
        'name' => 'login',
        'description' => 'Autenticación de usuario'
    ];

    public function type(): Type
    {
        return Type::string();
    }

    public function args(): array
    {
        return [
            'email' => ['type' => Type::nonNull(Type::string())],
            'password' => ['type' => Type::nonNull(Type::string())],
        ];
    }

    public function resolve($root, array $args, $context, ResolveInfo $resolveInfo, Closure $getSelectFields)
    {
        $usuario = User::where('email', $args['email'])->first();
        
        if ($usuario == null || !Hash::check($args['password'], $usuario->password)) {
            throw new \Exception('Credenciales inválidas');
        }

        $key = env('JWT_SECRET');
        $algoritmo = env('JWT_ALGORITHM');
        $time = time();
        
        $token = array(
            'iat' => $time,
            'exp' => $time + (1200 * 60),
            'data' => [
                'user_id' => $usuario->id,
            ],
        );

        return JWT::encode($token, $key, $algoritmo);
    }
}
