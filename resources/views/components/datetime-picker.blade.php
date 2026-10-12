@props([
    'name' => 'incident_date',
    'id' => 'incident_date',
    'label' => 'Date and time it happened',
    'value' => null,
    'max' => now()->format('Y-m-d\TH:i'),
    'required' => false,
])

@php
    $rawVal = old($name, $value);
    $initialDate = '';
    $initialHour12 = '12';
    $initialMinute = '00';
    $initialAmPm = 'PM';

    if ($rawVal) {
        try {
            $parsed = \Carbon\Carbon::parse($rawVal);
            $initialDate = $parsed->format('Y-m-d');
            $initialHour12 = $parsed->format('h');
            $initialMinute = $parsed->format('i');
            $initialAmPm = $parsed->format('A');
        } catch (\Throwable) {}
    }

    $hours = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
    $standardMinutes = ['00', '05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55'];
    if ($initialMinute && !in_array($initialMinute, $standardMinutes)) {
        $allMinutes = array_unique(array_merge($standardMinutes, [$initialMinute]));
        sort($allMinutes);
    } else {
        $allMinutes = $standardMinutes;
    }
    $maxDate = now()->format('Y-m-d');
@endphp

<div class="field dtp-container" data-dtp>
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:6px">
        <label for="{{ $id }}_date" style="margin-bottom:0">{{ $label }} <small class="muted">(optional)</small></label>
        <div class="dtp-shortcuts">
            <button type="button" class="dtp-chip" data-dtp-chip data-action="now">⚡ Just now</button>
            <button type="button" class="dtp-chip" data-dtp-chip data-action="1h">1 hr ago</button>
            <button type="button" class="dtp-chip" data-dtp-chip data-action="yesterday">Yesterday</button>
            <button type="button" class="dtp-chip clear-chip" data-dtp-chip data-action="clear">Clear</button>
        </div>
    </div>

    <!-- Hidden synced ISO input for Laravel backend -->
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ $rawVal }}" data-dtp-hidden>

    <div class="dtp-row">
        <!-- Date Selector (Native Date is fast without time-wheel lag) -->
        <div class="dtp-date-box">
            <input type="date" id="{{ $id }}_date" class="dtp-date-input" max="{{ $maxDate }}" value="{{ $initialDate }}" data-dtp-date aria-label="Incident Date">
        </div>

        <!-- Zero-Delay 12-Hour Time Selector -->
        <div class="dtp-time-box">
            <svg style="width:16px;height:16px;color:var(--muted);margin-left:4px;flex-shrink:0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>

            <select class="dtp-select" data-dtp-hour aria-label="Hour">
                @foreach ($hours as $h)
                    <option value="{{ $h }}" @selected($initialHour12 === $h)>{{ $h }}</option>
                @endforeach
            </select>

            <span class="dtp-sep">:</span>

            <select class="dtp-select" data-dtp-minute aria-label="Minute">
                @foreach ($allMinutes as $m)
                    <option value="{{ $m }}" @selected($initialMinute === $m)>{{ $m }}</option>
                @endforeach
            </select>

            <div class="dtp-ampm-group">
                <button type="button" class="dtp-ampm-btn {{ $initialAmPm === 'AM' ? 'active' : '' }}" data-dtp-ampm data-val="AM">AM</button>
                <button type="button" class="dtp-ampm-btn {{ $initialAmPm === 'PM' ? 'active' : '' }}" data-dtp-ampm data-val="PM">PM</button>
            </div>
        </div>
    </div>

    <div class="dtp-feedback">
        <span class="dtp-preview" data-dtp-preview>
            @if ($rawVal)
                Selected: <strong>{{ \Carbon\Carbon::parse($rawVal)->format('M j, Y \a\t g:i A') }}</strong>
            @else
                <span class="muted">No date and time chosen</span>
            @endif
        </span>
        <span class="dtp-warn" data-dtp-warn></span>
    </div>

    @error($name)<span class="field-error">{{ $message }}</span>@enderror
</div>

<script>
(function() {
    function initDtp() {
        document.querySelectorAll('[data-dtp]').forEach(function(widget) {
            if (widget.__dtpInit) return;
            widget.__dtpInit = true;

            const hidden = widget.querySelector('[data-dtp-hidden]');
            const dateInput = widget.querySelector('[data-dtp-date]');
            const hourSelect = widget.querySelector('[data-dtp-hour]');
            const minuteSelect = widget.querySelector('[data-dtp-minute]');
            const ampmBtns = widget.querySelectorAll('[data-dtp-ampm]');
            const preview = widget.querySelector('[data-dtp-preview]');
            const warn = widget.querySelector('[data-dtp-warn]');
            const chips = widget.querySelectorAll('[data-dtp-chip]');

            let currentAmPm = 'PM';
            const activeAmPm = widget.querySelector('[data-dtp-ampm].active');
            if (activeAmPm) {
                currentAmPm = activeAmPm.dataset.val;
            }

            function pad(n) { return String(n).padStart(2, '0'); }

            function updateHiddenAndPreview(skipClamp) {
                const d = dateInput.value;
                const h12 = parseInt(hourSelect.value, 10);
                const m = parseInt(minuteSelect.value, 10);

                if (!d) {
                    hidden.value = '';
                    preview.innerHTML = '<span class="muted">No date and time chosen</span>';
                    warn.style.display = 'none';
                    return;
                }

                let h24 = h12;
                if (currentAmPm === 'PM' && h12 < 12) h24 += 12;
                if (currentAmPm === 'AM' && h12 === 12) h24 = 0;

                const isoStr = `${d}T${pad(h24)}:${pad(m)}`;
                const selectedDate = new Date(`${d}T${pad(h24)}:${pad(m)}:00`);
                const graceTime = Date.now() + 5 * 60 * 1000;

                if (!isNaN(selectedDate.getTime()) && selectedDate.getTime() > graceTime) {
                    warn.style.display = 'inline';
                    warn.textContent = 'The incident date and time cannot be in the future.';
                } else {
                    warn.style.display = 'none';
                }

                hidden.value = isoStr;

                try {
                    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    const [yStr, mStr, dayStr] = d.split('-');
                    const mName = months[parseInt(mStr, 10) - 1] || mStr;
                    const formatted = `${mName} ${parseInt(dayStr, 10)}, ${yStr} at ${h12}:${pad(m)} ${currentAmPm}`;
                    preview.innerHTML = `Selected: <strong>${formatted}</strong>`;
                } catch(e) {
                    preview.innerHTML = `Selected: <strong>${isoStr}</strong>`;
                }
            }

            function setTimeFromDate(d) {
                dateInput.value = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
                const rawH = d.getHours();
                currentAmPm = rawH >= 12 ? 'PM' : 'AM';
                ampmBtns.forEach(b => b.classList.toggle('active', b.dataset.val === currentAmPm));

                const h12 = rawH % 12 || 12;
                hourSelect.value = pad(h12);

                const min = d.getMinutes();
                let hasOpt = Array.from(minuteSelect.options).some(o => parseInt(o.value, 10) === min);
                if (!hasOpt) {
                    const opt = new Option(pad(min), pad(min), true, true);
                    minuteSelect.add(opt);
                }
                minuteSelect.value = pad(min);

                updateHiddenAndPreview(true);
            }

            dateInput.addEventListener('change', () => updateHiddenAndPreview());
            dateInput.addEventListener('input', () => updateHiddenAndPreview());
            hourSelect.addEventListener('change', () => {
                if (!dateInput.value) {
                    const now = new Date();
                    dateInput.value = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
                }
                updateHiddenAndPreview();
            });
            minuteSelect.addEventListener('change', () => {
                if (!dateInput.value) {
                    const now = new Date();
                    dateInput.value = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
                }
                updateHiddenAndPreview();
            });

            ampmBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    currentAmPm = btn.dataset.val;
                    ampmBtns.forEach(b => b.classList.toggle('active', b === btn));
                    if (!dateInput.value) {
                        const now = new Date();
                        dateInput.value = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
                    }
                    updateHiddenAndPreview();
                });
            });

            chips.forEach(chip => {
                chip.addEventListener('click', (e) => {
                    e.preventDefault();
                    const action = chip.dataset.action;
                    const now = new Date();
                    if (action === 'now') {
                        setTimeFromDate(now);
                    } else if (action === '1h') {
                        const past = new Date(now.getTime() - 60 * 60 * 1000);
                        setTimeFromDate(past);
                    } else if (action === 'yesterday') {
                        const past = new Date(now.getTime() - 24 * 60 * 60 * 1000);
                        setTimeFromDate(past);
                    } else if (action === 'clear') {
                        dateInput.value = '';
                        hidden.value = '';
                        preview.innerHTML = '<span class="muted">No date and time chosen</span>';
                        warn.style.display = 'none';
                    }
                });
            });

            if (hidden.value) {
                updateHiddenAndPreview(true);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDtp);
    } else {
        initDtp();
    }
})();
</script>
