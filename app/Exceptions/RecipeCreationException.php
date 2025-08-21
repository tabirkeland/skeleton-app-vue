<?php

namespace App\Exceptions;

use Exception;
use Throwable;

/**
 * Exception thrown when recipe creation fails due to business logic or system issues.
 */
class RecipeCreationException extends Exception
{
    /**
     * Create a new recipe creation exception instance.
     *
     * @param string $message The exception message
     * @param int $code The exception code
     * @param Throwable|null $previous The previous exception
     */
    public function __construct(
        string $message = 'Recipe creation failed',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Report the exception for logging purposes.
     * 
     * @return bool Whether the exception should be reported
     */
    public function report(): bool
    {
        // Let Laravel's default exception handling log this
        // Custom logging could be added here if needed
        return true;
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request)
    {
        // For API requests (including GraphQL), return JSON response
        if ($request->expectsJson() || $request->is('graphql*')) {
            return response()->json([
                'error' => 'Recipe Creation Failed',
                'message' => $this->getMessage(),
            ], 422);
        }

        // For web requests, let Laravel handle it normally
        return false;
    }

    /**
     * Get the exception's context for logging.
     *
     * @return array
     */
    public function context(): array
    {
        return [
            'exception_type' => 'recipe_creation_failure',
            'user_message' => $this->getMessage(),
            'system_error' => $this->getPrevious()?->getMessage(),
        ];
    }
}