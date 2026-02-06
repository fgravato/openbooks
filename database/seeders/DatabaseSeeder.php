<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Identity\OrganizationSeeder;
use Database\Seeders\Identity\UserSeeder;
use Database\Seeders\Invoicing\InvoiceProfileSeeder;
use Database\Seeders\Invoicing\InvoiceSeeder;
use Database\Seeders\Invoicing\InvoiceTemplateSeeder;
use Database\Seeders\Payments\PaymentSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            OrganizationSeeder::class,
            UserSeeder::class,
            InvoiceTemplateSeeder::class,
            InvoiceProfileSeeder::class,
            InvoiceSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}
