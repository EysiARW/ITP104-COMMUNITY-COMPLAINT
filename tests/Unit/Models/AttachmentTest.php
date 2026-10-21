<?php

namespace Tests\Unit\Models;

use App\Models\Attachment;
use Tests\TestCase;

class AttachmentTest extends TestCase
{
    public function test_route_key_name_is_attachment_id(): void
    {
        $attachment = new Attachment();
        $this->assertSame('attachment_id', $attachment->getRouteKeyName());
    }

    public function test_is_image_detects_standard_mime_types(): void
    {
        $jpeg = new Attachment(['file_type' => 'image/jpeg']);
        $png = new Attachment(['file_type' => 'image/png']);
        $webp = new Attachment(['file_type' => 'image/webp']);
        $svg = new Attachment(['file_type' => 'image/svg+xml']);

        $this->assertTrue($jpeg->isImage());
        $this->assertTrue($png->isImage());
        $this->assertTrue($webp->isImage());
        $this->assertTrue($svg->isImage());
    }

    public function test_is_image_detects_extensions_when_mime_type_missing(): void
    {
        $jpg = new Attachment(['file_type' => 'application/octet-stream', 'file_name' => 'incident.jpg']);
        $pngUpper = new Attachment(['file_type' => null, 'file_name' => 'EVIDENCE.PNG']);
        $webp = new Attachment(['file_type' => '', 'file_name' => 'photo.webp']);

        $this->assertTrue($jpg->isImage());
        $this->assertTrue($pngUpper->isImage());
        $this->assertTrue($webp->isImage());
    }

    public function test_is_image_returns_false_for_documents_and_other_types(): void
    {
        $pdf = new Attachment(['file_type' => 'application/pdf', 'file_name' => 'report.pdf']);
        $docx = new Attachment(['file_type' => 'application/msword', 'file_name' => 'statement.docx']);
        $mp4 = new Attachment(['file_type' => 'video/mp4', 'file_name' => 'cctv.mp4']);

        $this->assertFalse($pdf->isImage());
        $this->assertFalse($docx->isImage());
        $this->assertFalse($mp4->isImage());
    }

    public function test_formatted_size_attribute_scales_properly(): void
    {
        $zero = new Attachment(['file_size' => 0]);
        $this->assertSame('0 B', $zero->formatted_size);

        $negative = new Attachment(['file_size' => -50]);
        $this->assertSame('0 B', $negative->formatted_size);

        $bytes = new Attachment(['file_size' => 512]);
        $this->assertSame('512 B', $bytes->formatted_size);

        $kb = new Attachment(['file_size' => 1024]);
        $this->assertSame('1.0 KB', $kb->formatted_size);

        $mb = new Attachment(['file_size' => 5242880]); // 5 MB
        $this->assertSame('5.0 MB', $mb->formatted_size);

        $gb = new Attachment(['file_size' => 1073741824]); // 1 GB
        $this->assertSame('1.0 GB', $gb->formatted_size);
    }
}
