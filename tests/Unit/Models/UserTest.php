<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_is_admin_returns_true_for_admin_role_only(): void
    {
        $admin = new User(['role_id' => User::ROLE_ADMIN]);
        $complainant = new User(['role_id' => User::ROLE_COMPLAINANT]);
        $guest = new User(['role_id' => null]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($complainant->isAdmin());
        $this->assertFalse($guest->isAdmin());
    }

    public function test_full_name_attribute_concatenates_and_trims_correctly(): void
    {
        $user = new User([
            'first_name' => ' Juan ',
            'last_name' => ' Dela Cruz ',
        ]);

        $this->assertSame('Juan   Dela Cruz', $user->full_name);

        $singleNameUser = new User([
            'first_name' => 'Maria',
            'last_name' => '',
        ]);

        $this->assertSame('Maria', $singleNameUser->full_name);
    }

    public function test_initials_attribute_extracts_two_uppercase_letters(): void
    {
        $user = new User([
            'first_name' => 'juan',
            'last_name' => 'dela cruz',
        ]);
        $this->assertSame('JD', $user->initials);

        $unicodeUser = new User([
            'first_name' => 'ñaña',
            'last_name' => 'ortega',
        ]);
        $this->assertSame('ÑO', $unicodeUser->initials);

        $emptyUser = new User([
            'first_name' => '',
            'last_name' => '',
        ]);
        $this->assertSame('U', $emptyUser->initials);
    }

    public function test_auth_password_column_is_password_hash(): void
    {
        $user = new User([
            'password_hash' => '$2y$10$abcdefghijklmnopqrstuvwxyz123456',
        ]);

        $this->assertSame('password_hash', $user->getAuthPasswordName());
        $this->assertSame('$2y$10$abcdefghijklmnopqrstuvwxyz123456', $user->getAuthPassword());
    }

    public function test_verification_status_helper_methods(): void
    {
        $pendingUser = new User(['verification_status' => User::STATUS_PENDING]);
        $this->assertTrue($pendingUser->isPendingVerification());
        $this->assertFalse($pendingUser->isVerificationRejected());

        $rejectedUser = new User(['verification_status' => User::STATUS_REJECTED]);
        $this->assertFalse($rejectedUser->isPendingVerification());
        $this->assertTrue($rejectedUser->isVerificationRejected());

        $approvedUser = new User(['verification_status' => User::STATUS_APPROVED]);
        $this->assertFalse($approvedUser->isPendingVerification());
        $this->assertFalse($approvedUser->isVerificationRejected());
    }

    public function test_casts_and_hidden_attributes_security(): void
    {
        $user = new User([
            'password_hash' => 'secret123',
            'remember_token' => 'token123',
            'first_name' => 'Cardo',
        ]);

        $array = $user->toArray();
        $this->assertArrayNotHasKey('password_hash', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
        $this->assertArrayHasKey('first_name', $array);
    }
}
