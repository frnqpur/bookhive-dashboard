<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApiDocsController extends Controller
{
    public function public(): Response
    {
        return Inertia::render('Public/ApiDocs', $this->documentationPayload());
    }

    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('api-docs.access'), 403, 'You do not have permission to access API documentation.');

        return Inertia::render('global/ApiDocs/Index', $this->documentationPayload([
            'pageTitle' => 'API Docs',
            'pageDescription' => 'JWT API reference for BookHive Dashboard portfolio testing.',
            'isDashboard' => true,
        ]));
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function documentationPayload(array $overrides = []): array
    {
        return array_merge([
            'pageTitle' => 'BookHive API Docs',
            'pageDescription' => 'Public and JWT-protected endpoints for the BookHive Dashboard portfolio API.',
            'basePath' => url('/api/client'),
            'demoCredentials' => User::DEMO_CREDENTIALS,
            'authNotes' => [
                'Use POST /api/client/login to receive a JWT bearer token.',
                'Send protected requests with Authorization: Bearer <token>.',
                'Owner credentials are private and are never shown in public documentation.',
                'The public demo resets automatically every 6 hours, so demo data may be restored during testing.',
                'Production reset uses php artisan demo:reset through Laravel Scheduler; do not use migrate:fresh for public demo reset.',
            ],
            'endpoints' => [
                [
                    'method' => 'POST',
                    'path' => '/register',
                    'auth' => 'Public',
                    'description' => 'Create a public account. Allowed roles: Admin, Editor, Reviewer, Customer. Protected owner access is rejected by backend validation.',
                    'body' => [
                        'name' => 'Demo Reviewer',
                        'email' => 'demo-reviewer@example.com',
                        'password' => 'password123',
                        'password_confirmation' => 'password123',
                        'role' => 'Reviewer',
                    ],
                ],
                [
                    'method' => 'POST',
                    'path' => '/login',
                    'auth' => 'Public',
                    'description' => 'Authenticate with email/password and receive a JWT token.',
                    'body' => [
                        'email' => 'reviewer@demo.com',
                        'password' => 'password',
                    ],
                ],
                [
                    'method' => 'GET',
                    'path' => '/books?search=clean&per_page=8',
                    'auth' => 'Public',
                    'description' => 'List published books with safe search, sort, and pagination.',
                ],
                [
                    'method' => 'GET',
                    'path' => '/books/{id-or-slug}',
                    'auth' => 'Public',
                    'description' => 'Read one published book by numeric ID or slug. Includes a preview of approved reviews.',
                ],
                [
                    'method' => 'GET',
                    'path' => '/books/{id-or-slug}/reviews',
                    'auth' => 'Public',
                    'description' => 'List approved reviews only for a published book.',
                ],
                [
                    'method' => 'POST',
                    'path' => '/logout',
                    'auth' => 'Bearer token',
                    'description' => 'Invalidate the current JWT token where supported by the JWT driver.',
                ],
                [
                    'method' => 'GET',
                    'path' => '/me',
                    'auth' => 'Bearer token',
                    'description' => 'Return the current authenticated API user, roles, and permissions.',
                ],
                [
                    'method' => 'GET',
                    'path' => '/my-reviews?status=pending',
                    'auth' => 'Bearer token',
                    'description' => 'List reviews created by the current authenticated user.',
                ],
                [
                    'method' => 'POST',
                    'path' => '/books/{id-or-slug}/reviews',
                    'auth' => 'Bearer token',
                    'description' => 'Create a pending review for a published book. Approved/rejected moderation remains a dashboard workflow.',
                    'body' => [
                        'rating' => 5,
                        'title' => 'A clear and useful book',
                        'body' => 'This book is well structured and easy to follow.',
                    ],
                ],
                [
                    'method' => 'PATCH',
                    'path' => '/reviews/{review}',
                    'auth' => 'Bearer token',
                    'description' => 'Update your own pending/rejected review. Approved reviews cannot be edited through the public API.',
                    'body' => [
                        'rating' => 4,
                        'title' => 'Updated review title',
                        'body' => 'Updated review body.',
                    ],
                ],
                [
                    'method' => 'DELETE',
                    'path' => '/reviews/{review}',
                    'auth' => 'Bearer token',
                    'description' => 'Delete your own pending/rejected review if allowed. Protected seeded reviews cannot be deleted by public/demo users.',
                ],
            ],
            'responseExamples' => [
                'success' => [
                    'success' => true,
                    'message' => 'Request completed successfully.',
                    'data' => ['example' => '...'],
                ],
                'validation' => [
                    'success' => false,
                    'message' => 'Validation failed.',
                    'data' => [
                        'errors' => [
                            'role' => ['The selected role is invalid.'],
                        ],
                    ],
                ],
                'unauthorized' => [
                    'success' => false,
                    'message' => 'JWT token is missing. Send an Authorization: Bearer <token> header.',
                    'data' => ['code' => 'token_missing'],
                ],
                'forbidden' => [
                    'success' => false,
                    'message' => 'You can only update your own pending or rejected reviews through the API.',
                    'data' => null,
                ],
                'notFound' => [
                    'success' => false,
                    'message' => 'Book not found.',
                    'data' => null,
                ],
            ],
            'curlExamples' => [
                'login' => "curl -X POST " . url('/api/client/login') . " \\\n  -H 'Accept: application/json' \\\n  -H 'Content-Type: application/json' \\\n  -d '{\"email\":\"reviewer@demo.com\",\"password\":\"password\"}'",
                'me' => "curl " . url('/api/client/me') . " \\\n  -H 'Accept: application/json' \\\n  -H 'Authorization: Bearer YOUR_TOKEN_HERE'",
                'createReview' => "curl -X POST " . url('/api/client/books/1/reviews') . " \\\n  -H 'Accept: application/json' \\\n  -H 'Content-Type: application/json' \\\n  -H 'Authorization: Bearer YOUR_TOKEN_HERE' \\\n  -d '{\"rating\":5,\"title\":\"Great read\",\"body\":\"Useful and easy to follow.\"}'",
            ],
        ], $overrides);
    }
}
