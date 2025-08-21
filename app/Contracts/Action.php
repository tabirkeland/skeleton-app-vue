<?php

namespace App\Contracts;

/**
 * Action Contract
 * 
 * Defines the standard interface for all Action classes in the application.
 * Actions implement the Command pattern, encapsulating business logic
 * into single-responsibility classes.
 * 
 * This contract ensures consistency across all actions and demonstrates
 * Laravel architecture best practices.
 */
interface Action
{
    /**
     * Execute the action with the given parameters.
     * 
     * All actions must implement this method to perform their
     * specific business logic. Input should be validated before
     * reaching the action (e.g., via GraphQL schema validation).
     * 
     * @param array $parameters The parameters for the action
     * @return mixed The result of the action execution
     */
    public function execute(array $parameters = []): mixed;
}