<?php
/**
 * Mock for the FallbackEngine
 * Adds a public function to force the engine to fallback to the secondary
 */
class FallbackMockEngine extends FallbackEngine
{
    public function fallback($setPrimary = false)
    {
        parent::fallback();
    }
}
