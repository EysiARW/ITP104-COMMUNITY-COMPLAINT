{{-- FILE UPLOAD WIDGET (reusable). Usage: <x-file-upload name="evidence" :max="5" :max-size="5" />
     Posts as name[]; validate server-side with 'name.*' => ['file','mimes:...','max:5120']. --}}
@props(['name' => 'evidence', 'label' => 'Attach photos or documents', 'max' => 5, 'maxSize' => 5, 'types' => 'jpg,jpeg,png,webp,pdf'])
<div class="field" data-file-upload data-max="{{ $max }}" data-max-size="{{ $maxSize }}" data-types="{{ $types }}">
    <span class="label">{{ $label }} <span class="muted">(optional)</span></span>
    <div class="dropzone" data-dropzone tabindex="0" role="button" aria-label="Choose files to upload">
        <x-icon name="upload" style="width:30px;height:30px;margin:0 auto 6px;color:#3552e0" />
        <div><strong>Click to browse</strong> or drag and drop files here</div>
        <div class="hint">Up to {{ $max }} files, {{ $maxSize }} MB each &middot; {{ strtoupper(str_replace(',', ', ', $types)) }}</div>
    </div>
    <input type="file" name="{{ $name }}[]" multiple hidden accept=".{{ str_replace(',', ',.', $types) }}">
    <ul class="file-list" data-file-list></ul>
    <span class="field-error" data-file-error>{{ $errors->first($name) ?: $errors->first($name . '.*') }}</span>
</div>
