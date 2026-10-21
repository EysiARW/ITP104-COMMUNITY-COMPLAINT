<?php

namespace Tests\Unit\Validation;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class DomainValidationRulesTest extends TestCase
{
    public function test_incident_date_validation_allows_up_to_five_minute_clock_drift(): void
    {
        // Enforce Asia/Manila baseline
        $now = Carbon::now('Asia/Manila');
        Carbon::setTestNow($now);

        $rules = [
            'incident_date' => ['nullable', 'date', 'before_or_equal:' . now()->addMinutes(5)->toDateTimeString()],
        ];
        $messages = [
            'incident_date.before_or_equal' => 'The incident date and time cannot be in the future.',
        ];

        // 1. Past date (yesterday)
        $past = Validator::make(['incident_date' => $now->copy()->subDay()->format('Y-m-d H:i:s')], $rules, $messages);
        $this->assertFalse($past->fails());

        // 2. Exact current moment
        $current = Validator::make(['incident_date' => $now->format('Y-m-d H:i:s')], $rules, $messages);
        $this->assertFalse($current->fails());

        // 3. 4 minutes in the future (within 5-min clock drift tolerance)
        $withinTolerance = Validator::make(['incident_date' => $now->copy()->addMinutes(4)->format('Y-m-d H:i:s')], $rules, $messages);
        $this->assertFalse($withinTolerance->fails());

        // 4. Exactly 5 minutes in the future (boundary)
        $boundary = Validator::make(['incident_date' => $now->copy()->addMinutes(5)->format('Y-m-d H:i:s')], $rules, $messages);
        $this->assertFalse($boundary->fails());

        // 5. 6 minutes in the future (exceeds clock drift tolerance)
        $exceeded = Validator::make(['incident_date' => $now->copy()->addMinutes(6)->format('Y-m-d H:i:s')], $rules, $messages);
        $this->assertTrue($exceeded->fails());
        $this->assertSame(
            'The incident date and time cannot be in the future.',
            $exceeded->errors()->first('incident_date')
        );

        Carbon::setTestNow(); // Reset test clock
    }

    public function test_philippine_mobile_number_regex_validation(): void
    {
        $rule = ['contact_number' => ['required', 'regex:/^(09|\+639)\d{9}$/']];

        $validNumbers = [
            '09171234567',
            '09981234567',
            '+639171234567',
            '+639209876543',
        ];

        foreach ($validNumbers as $number) {
            $validator = Validator::make(['contact_number' => $number], $rule);
            $this->assertFalse($validator->fails(), "Failed asserting that {$number} is recognized as a valid Philippine mobile number.");
        }

        $invalidNumbers = [
            '08171234567',    // Invalid prefix
            '0917123456',     // 10 digits instead of 11
            '091712345678',   // 12 digits
            '+638123456789',  // Invalid international prefix
            '+63917123456',   // Short international
            'phone12345',     // Non-numeric
            '',
        ];

        foreach ($invalidNumbers as $number) {
            $validator = Validator::make(['contact_number' => $number], $rule);
            $this->assertTrue($validator->fails(), "Failed asserting that {$number} is rejected as an invalid mobile number.");
        }
    }

    public function test_verification_document_type_whitelist(): void
    {
        $rule = ['verification_doc_type' => ['required', 'string', 'in:voters_cert,valid_id,certificate_of_residency']];

        foreach (['voters_cert', 'valid_id', 'certificate_of_residency'] as $validType) {
            $validator = Validator::make(['verification_doc_type' => $validType], $rule);
            $this->assertFalse($validator->fails(), "Failed asserting that {$validType} is a valid document type.");
        }

        foreach (['passport', 'driver_license', 'random_string', ''] as $invalidType) {
            $validator = Validator::make(['verification_doc_type' => $invalidType], $rule);
            $this->assertTrue($validator->fails(), "Failed asserting that {$invalidType} is rejected.");
        }
    }
}
