<?php

namespace IslamWiki\Middleware;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Authentication\AuthService;

/**
 * Authentication Middleware
 * 
 * @author Khalid Abdullah
 * @version 0.0.1
 * @date 2025-08-30
 * @license AGPL-3.0
 */
class AuthMiddleware
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle the request
     */
    public function handle(Request $request, callable $next): Response
    {
        $token = $request->header('Authorization');
        
        if (!$token || strpos($token, 'Bearer ') !== 0) {
            return (new Response())->json([
                'success' => false,
                'error' => 'Authentication required',
                'message' => 'Valid JWT token is required'
            ], 401);
        }

        $token = substr($token, 7);
        $user = $this->authService->validateToken($token);

        if (!$user) {
            return (new Response())->json([
                'success' => false,
                'error' => 'Invalid token',
                'message' => 'Token is invalid or expired'
            ], 401);
        }

        // Add user to request for downstream handlers
        $request->setUser($user);

        return $next($request);
    }
} 