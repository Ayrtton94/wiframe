<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Suppliers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierCreationTest extends TestCase
{
    
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::query()->firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
    }

    public function test_admin_can_create_supplier_with_formatted_contact_and_bank_fields(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post(route('suppliers.store'), [
            'ruc' => ' 20123456789 ',
            'company_name' => 'Textiles Andinos SAC',
            'category' => 'Textiles',
            'phone' => '+51 999 999 999',
            'email' => 'contacto@textiles.test',
            'name' => 'Juan Perez',
            'address' => 'Av. Principal 123',
            'city' => 'Lima',
            'country' => 'Peru',
            'account' => '001-234-5678901234',
            'cod_swift' => 'abcdpemx',
            'bank_name' => 'Banco de Credito del Peru',
            'bank_address' => 'Calle Banco 456',
            'bank_city' => 'Lima',
            'bank_country' => 'Peru',
            'bank_cod_swift' => 'bcplpemm',
            'bank_name2' => null,
            'bank_address2' => null,
            'bank_cod_swift2' => '',
            'others' => 'Proveedor recurrente',
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('suppliers', [
            'email' => 'contacto@textiles.test',
        ]);
    }
}
