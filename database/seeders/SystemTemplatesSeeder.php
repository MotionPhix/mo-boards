<?php

namespace Database\Seeders;

use App\Models\ContractTemplate;
use Illuminate\Database\Seeder;

class SystemTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Professional Billboard Advertising Agreement',
                'description' => 'A comprehensive billboard advertising contract template suitable for most advertising agencies and outdoor media companies.',
                'category' => 'Billboard Advertising',
                'price' => 29.99,
                'features' => 'Professional formatting, Legal compliance, Customizable terms, Payment schedules, Multiple billboard support',
                'tags' => ['billboard', 'advertising', 'professional', 'comprehensive'],
                'content' => $this->getProfessionalBillboardContent(),
            ],
            [
                'name' => 'Simple Billboard Rental Agreement',
                'description' => 'A straightforward template for basic billboard rental agreements.',
                'category' => 'Billboard Advertising',
                'price' => 19.99,
                'features' => 'Easy to use, Basic terms, Quick setup, Perfect for small businesses',
                'tags' => ['billboard', 'simple', 'rental', 'basic'],
                'content' => $this->getSimpleBillboardContent(),
            ],
            [
                'name' => 'Digital Billboard Advertising Contract',
                'description' => 'Specialized template for digital billboard advertising with rotation schedules and content specifications.',
                'category' => 'Digital Advertising',
                'price' => 39.99,
                'features' => 'Digital specifications, Content rotation, Technical requirements, Performance metrics',
                'tags' => ['digital', 'billboard', 'rotation', 'technical'],
                'content' => $this->getDigitalBillboardContent(),
            ],
            [
                'name' => 'Transit Advertising Agreement',
                'description' => 'Complete contract template for bus, train, and other transit advertising.',
                'category' => 'Transit Advertising',
                'price' => 34.99,
                'features' => 'Multi-vehicle support, Route specifications, Duration tracking, Compliance clauses',
                'tags' => ['transit', 'bus', 'train', 'mobile'],
                'content' => $this->getTransitAdvertisingContent(),
            ],
            [
                'name' => 'Event Sponsorship Contract',
                'description' => 'Professional template for event sponsorship agreements with billboard placements.',
                'category' => 'Event Marketing',
                'price' => 24.99,
                'features' => 'Event integration, Sponsorship tiers, Brand visibility, ROI tracking',
                'tags' => ['event', 'sponsorship', 'branding', 'marketing'],
                'content' => $this->getEventSponsorshipContent(),
            ],
            [
                'name' => 'Premium Outdoor Media Package',
                'description' => 'Comprehensive template for premium outdoor advertising campaigns across multiple media types.',
                'category' => 'Premium Services',
                'price' => 49.99,
                'features' => 'Multi-media support, Campaign management, Premium positioning, Detailed reporting',
                'tags' => ['premium', 'multimedia', 'campaign', 'comprehensive'],
                'content' => $this->getPremiumOutdoorContent(),
            ],
        ];

        foreach ($templates as $template) {
            ContractTemplate::create([
                'company_id' => null, // System template
                'name' => $template['name'],
                'description' => $template['description'],
                'content' => $template['content'],
                'default_terms' => $this->getDefaultTerms(),
                'custom_fields' => [],
                'is_active' => true,
                'is_system_template' => true,
                'price' => $template['price'],
                'category' => $template['category'],
                'features' => $template['features'],
                'tags' => $template['tags'],
            ]);
        }
    }

    private function getProfessionalBillboardContent(): string
    {
        return '
        <div style="max-width: 800px; margin: 0 auto; font-family: Arial, sans-serif; line-height: 1.6;">
            {{company_logo}}

            <h1 style="text-align: center; color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px;">
                PROFESSIONAL BILLBOARD ADVERTISING AGREEMENT
            </h1>

            <div style="background: #f8f9fa; padding: 20px; border-left: 5px solid #3498db; margin: 20px 0;">
                <p><strong>Agreement Date:</strong> {{today_date}}</p>
                <p><strong>Contract Number:</strong> {{contract_number}}</p>
            </div>

            <h2 style="color: #2c3e50;">PARTIES TO THE AGREEMENT</h2>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin: 20px 0;">
                <div style="background: #ffffff; padding: 15px; border: 1px solid #ddd; border-radius: 5px;">
                    <h3 style="color: #e74c3c; margin-top: 0;">ADVERTISER (CLIENT)</h3>
                    <p><strong>Name:</strong> {{client_name}}</p>
                    <p><strong>Company:</strong> {{client_company}}</p>
                    <p><strong>Address:</strong> {{client_address}}</p>
                    <p><strong>Email:</strong> {{client_email}}</p>
                    <p><strong>Phone:</strong> {{client_phone}}</p>
                </div>

                <div style="background: #ffffff; padding: 15px; border: 1px solid #ddd; border-radius: 5px;">
                    <h3 style="color: #27ae60; margin-top: 0;">MEDIA COMPANY</h3>
                    <p><strong>Company:</strong> {{company_name}}</p>
                    <p><strong>Address:</strong> {{company_address}}</p>
                    <p><strong>Email:</strong> {{company_email}}</p>
                    <p><strong>Phone:</strong> {{company_phone}}</p>
                    <p><strong>Website:</strong> {{company_website}}</p>
                </div>
            </div>

            <h2 style="color: #2c3e50;">BILLBOARD ADVERTISING SERVICES</h2>
            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;">
                <p><strong>Billboard Locations:</strong></p>
                <p>{{billboard_locations}}</p>
            </div>

            <h2 style="color: #2c3e50;">CONTRACT TERMS</h2>

            <div style="background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;">
                <h3 style="margin-top: 0;">Duration & Dates</h3>
                <p><strong>Start Date:</strong> {{start_date}}</p>
                <p><strong>End Date:</strong> {{end_date}}</p>
                <p><strong>Contract Status:</strong> {{status}}</p>
            </div>

            <div style="background: #cce5ff; padding: 15px; border-radius: 5px; margin: 15px 0;">
                <h3 style="margin-top: 0;">Financial Terms</h3>
                <p><strong>Monthly Rate:</strong> {{monthly_amount}}</p>
                <p><strong>Total Contract Value:</strong> {{total_amount}}</p>
                <p><strong>Payment Terms:</strong> {{payment_terms}}</p>
                <p><strong>Currency:</strong> {{currency}}</p>
            </div>

            <h2 style="color: #2c3e50;">TERMS AND CONDITIONS</h2>

            <div style="padding: 20px; background: #f8f9fa; border-radius: 5px;">
                <h3>1. Installation and Maintenance</h3>
                <p>The Media Company shall be responsible for the professional installation and ongoing maintenance of all advertising materials at the specified billboard locations.</p>

                <h3>2. Content Approval</h3>
                <p>All advertising content must be approved by the Media Company prior to installation and must comply with local regulations and company standards.</p>

                <h3>3. Payment Terms</h3>
                <p>Payment shall be made according to the specified payment schedule. Late payments may incur additional fees as outlined in our credit policy.</p>

                <h3>4. Cancellation Policy</h3>
                <p>Either party may terminate this agreement with 30 days written notice. Early termination fees may apply as specified in the terms.</p>

                <h3>5. Force Majeure</h3>
                <p>Neither party shall be liable for delays or failures in performance resulting from circumstances beyond their reasonable control.</p>
            </div>

            <div style="margin-top: 50px; display: grid; grid-template-columns: 1fr 1fr; gap: 50px;">
                <div style="text-align: center;">
                    <div style="border-bottom: 2px solid #333; width: 200px; margin: 0 auto 10px;"></div>
                    <p><strong>Client Signature</strong></p>
                    <p>{{client_name}}</p>
                    <p>Date: _______________</p>
                </div>

                <div style="text-align: center;">
                    <div style="border-bottom: 2px solid #333; width: 200px; margin: 0 auto 10px;"></div>
                    <p><strong>Company Representative</strong></p>
                    <p>{{company_name}}</p>
                    <p>Date: _______________</p>
                </div>
            </div>

            <div style="margin-top: 30px; padding: 15px; background: #e8f4f8; border-radius: 5px; text-align: center; font-size: 12px; color: #666;">
                <p>This is a legally binding agreement. Please read all terms carefully before signing.</p>
                <p>For questions about this contract, contact {{company_email}} or {{company_phone}}</p>
            </div>
        </div>';
    }

    private function getSimpleBillboardContent(): string
    {
        return '
        <div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif;">
            <h1 style="text-align: center;">BILLBOARD RENTAL AGREEMENT</h1>

            <p><strong>Date:</strong> {{today_date}}</p>
            <p><strong>Contract #:</strong> {{contract_number}}</p>

            <h2>Parties</h2>
            <p><strong>Client:</strong> {{client_name}} ({{client_company}})</p>
            <p><strong>Provider:</strong> {{company_name}}</p>

            <h2>Billboard Details</h2>
            <p><strong>Locations:</strong> {{billboard_locations}}</p>
            <p><strong>Period:</strong> {{start_date}} to {{end_date}}</p>

            <h2>Payment</h2>
            <p><strong>Monthly Rate:</strong> {{monthly_amount}}</p>
            <p><strong>Total Amount:</strong> {{total_amount}}</p>
            <p><strong>Payment Schedule:</strong> {{payment_terms}}</p>

            <h2>Terms</h2>
            <ul>
                <li>Payment due on the first of each month</li>
                <li>30-day notice required for cancellation</li>
                <li>All content subject to approval</li>
                <li>Installation and maintenance included</li>
            </ul>

            <div style="margin-top: 50px;">
                <p>Client Signature: _________________________ Date: _______</p>
                <p>Provider Signature: _______________________ Date: _______</p>
            </div>
        </div>';
    }

    private function getDigitalBillboardContent(): string
    {
        return '
        <div style="max-width: 800px; margin: 0 auto; font-family: Arial, sans-serif;">
            <h1 style="text-align: center; color: #1a365d;">DIGITAL BILLBOARD ADVERTISING CONTRACT</h1>

            <div style="background: #e6fffa; padding: 15px; border-left: 4px solid #00b894;">
                <h2>DIGITAL SPECIFICATIONS</h2>
                <ul>
                    <li><strong>Resolution:</strong> 1920x1080 pixels minimum</li>
                    <li><strong>File Format:</strong> MP4, JPG, PNG accepted</li>
                    <li><strong>Duration:</strong> 10-15 seconds per rotation</li>
                    <li><strong>Rotation Cycle:</strong> Every 60 seconds</li>
                </ul>
            </div>

            <h2>CONTRACT DETAILS</h2>
            <p><strong>Client:</strong> {{client_name}} ({{client_company}})</p>
            <p><strong>Provider:</strong> {{company_name}}</p>
            <p><strong>Digital Locations:</strong> {{billboard_locations}}</p>
            <p><strong>Campaign Period:</strong> {{start_date}} to {{end_date}}</p>

            <h2>FINANCIAL TERMS</h2>
            <p><strong>Monthly Investment:</strong> {{monthly_amount}}</p>
            <p><strong>Total Campaign Value:</strong> {{total_amount}}</p>

            <h2>CONTENT MANAGEMENT</h2>
            <ul>
                <li>Content changes allowed up to 2 times per month</li>
                <li>24-hour notice required for content updates</li>
                <li>All content must pass technical and content review</li>
                <li>Backup static image required for technical issues</li>
            </ul>

            <div style="margin-top: 40px;">
                <p>Signatures:</p>
                <p>Client: _________________________ Date: _______</p>
                <p>Digital Media Manager: _____________ Date: _______</p>
            </div>
        </div>';
    }

    private function getTransitAdvertisingContent(): string
    {
        return '
        <h1>TRANSIT ADVERTISING AGREEMENT</h1>
        <p>Client: {{client_name}}</p>
        <p>Transit Authority: {{company_name}}</p>
        <p>Routes: {{billboard_locations}}</p>
        <p>Campaign Duration: {{start_date}} to {{end_date}}</p>
        <p>Investment: {{total_amount}}</p>
        <!-- Transit-specific terms would be detailed here -->
        ';
    }

    private function getEventSponsorshipContent(): string
    {
        return '
        <h1>EVENT SPONSORSHIP AGREEMENT</h1>
        <p>Sponsor: {{client_name}}</p>
        <p>Event Organizer: {{company_name}}</p>
        <p>Event Locations: {{billboard_locations}}</p>
        <p>Sponsorship Period: {{start_date}} to {{end_date}}</p>
        <p>Sponsorship Investment: {{total_amount}}</p>
        <!-- Event sponsorship terms would be detailed here -->
        ';
    }

    private function getPremiumOutdoorContent(): string
    {
        return '
        <h1>PREMIUM OUTDOOR MEDIA CAMPAIGN</h1>
        <p>Advertiser: {{client_name}}</p>
        <p>Media Partner: {{company_name}}</p>
        <p>Premium Locations: {{billboard_locations}}</p>
        <p>Campaign Period: {{start_date}} to {{end_date}}</p>
        <p>Total Investment: {{total_amount}}</p>
        <!-- Premium campaign terms would be detailed here -->
        ';
    }

    private function getDefaultTerms(): array
    {
        return [
            'installation_responsibility' => 'Media Company handles all installation and maintenance',
            'content_approval' => 'All content subject to approval before display',
            'payment_schedule' => 'Monthly payments due on the 1st of each month',
            'cancellation_notice' => '30 days written notice required for cancellation',
            'force_majeure' => 'Neither party liable for circumstances beyond reasonable control',
            'governing_law' => 'Agreement governed by local jurisdiction laws',
        ];
    }
}
