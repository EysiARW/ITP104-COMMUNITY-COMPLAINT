<?php

namespace Tests\Unit\Models;

use App\Models\Complaint;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class ComplaintTest extends TestCase
{
    public function test_route_key_name_is_reference_no(): void
    {
        $complaint = new Complaint();
        $this->assertSame('reference_no', $complaint->getRouteKeyName());
    }

    public function test_masked_complainant_returns_guest_anonymous_when_no_user_present(): void
    {
        $complaint = new Complaint();
        $complaint->setRelation('complainant', null);

        $this->assertSame('Guest / Anonymous', $complaint->masked_complainant);
    }

    public function test_masked_complainant_masks_first_and_last_name(): void
    {
        $user = new User([
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
        ]);
        $complaint = new Complaint();
        $complaint->setRelation('complainant', $user);

        // 'Juan' (length 4) -> 'J' . str_repeat('*', min(4, max(2, 3))) => 'J***'
        // 'Cruz' (length 4) -> 'C' . str_repeat('*', min(4, max(2, 3))) => 'C***'
        $this->assertSame('J*** C***', $complaint->masked_complainant);
    }

    public function test_masked_complainant_handles_single_letter_names(): void
    {
        $user = new User([
            'first_name' => 'J',
            'last_name' => 'C',
        ]);
        $complaint = new Complaint();
        $complaint->setRelation('complainant', $user);

        $this->assertSame('J*** C***', $complaint->masked_complainant);
    }

    public function test_masked_complainant_handles_empty_complainant_names_fallback(): void
    {
        $user = new User([
            'first_name' => '',
            'last_name' => '',
        ]);
        $complaint = new Complaint();
        $complaint->setRelation('complainant', $user);

        $this->assertSame('Resident', $complaint->masked_complainant);
    }

    public function test_incident_date_cast_as_datetime(): void
    {
        $complaint = new Complaint([
            'incident_date' => '2026-10-15 14:30:00',
        ]);

        $this->assertInstanceOf(Carbon::class, $complaint->incident_date);
        $this->assertSame('2026-10-15 14:30:00', $complaint->incident_date->format('Y-m-d H:i:s'));
    }
}
