<?php

// ============================================================================
// FILE: src/Services/ModuleService.php
// ============================================================================

class ModuleService implements ServiceInterface
{
    use RequestTrait, ResponseTrait;
    private SalesPro $client;
    
    public function __construct(SalesPro $client) { $this->client = $client; }
    public function getClient(): SalesPro { return $this->client; }
    
    /** Install module */
    public function install(string $moduleName): array { return $this->post('/connector/api/install-module', ['module_name' => $moduleName]); }
    
    /** Install connector module */
    public function installConnector(): array { return $this->post('/connector/api/installing-connector'); }
    
    /** Uninstall module */
    public function uninstall(string $moduleName): array { return $this->post('/connector/api/uninstall-module', ['module_name' => $moduleName]); }
    
    /** Update module */
    public function update(string $moduleName): array { return $this->post('/connector/api/update-module', ['module_name' => $moduleName]); }
}