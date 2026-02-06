<?php

declare(strict_types=1);

namespace Database\Seeders\Invoicing;

use App\Domains\Identity\Models\Organization;
use App\Domains\Invoicing\Models\InvoiceTemplate;
use Illuminate\Database\Seeder;

class InvoiceTemplateSeeder extends Seeder
{
    public function run(): void
    {
        Organization::query()->each(function (Organization $organization): void {
            $templates = [
                [
                    'name' => 'default',
                    'is_default' => true,
                    'header_html' => '<h1 style="margin:0;">{{invoice_number}}</h1>',
                    'footer_html' => '<p style="font-size:12px;">{{terms}}</p>',
                    'css_styles' => 'body { font-family: Helvetica, Arial, sans-serif; color:#0f172a; }',
                    'logo_position' => 'left',
                    'color_primary' => '#0f172a',
                    'color_secondary' => '#334155',
                    'paper_size' => 'A4',
                ],
                [
                    'name' => 'modern',
                    'is_default' => false,
                    'header_html' => '<h1 style="margin:0; color:#0ea5e9;">{{invoice_number}}</h1>',
                    'footer_html' => '<p style="font-size:12px; color:#64748b;">{{notes}}</p>',
                    'css_styles' => 'body { font-family: "Trebuchet MS", sans-serif; color:#0f172a; }',
                    'logo_position' => 'center',
                    'color_primary' => '#0ea5e9',
                    'color_secondary' => '#14b8a6',
                    'paper_size' => 'A4',
                ],
                [
                    'name' => 'classic',
                    'is_default' => false,
                    'header_html' => '<h1 style="margin:0; font-family: Georgia, serif;">{{invoice_number}}</h1>',
                    'footer_html' => '<p style="font-size:12px; font-family: Georgia, serif;">{{terms}}</p>',
                    'css_styles' => 'body { font-family: Georgia, serif; color:#1e293b; }',
                    'logo_position' => 'right',
                    'color_primary' => '#1e293b',
                    'color_secondary' => '#475569',
                    'paper_size' => 'Letter',
                ],
            ];

            foreach ($templates as $template) {
                InvoiceTemplate::query()->updateOrCreate(
                    [
                        'organization_id' => $organization->id,
                        'name' => $template['name'],
                    ],
                    $template,
                );
            }
        });
    }
}
