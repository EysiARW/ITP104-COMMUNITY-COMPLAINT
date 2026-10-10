{{-- CONFIRMATION MODAL. Included once per layout.
     Usage: <button type="submit" data-confirm="Message" data-confirm-title="Title" data-confirm-ok="Yes, delete" data-confirm-type="danger">
     The button must be inside a <form>; the form submits only after the user confirms. --}}
<div class="modal" id="confirmModal" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
    <div class="modal-box">
        <h3 id="confirmTitle" data-modal-title>Are you sure?</h3>
        <p class="muted" data-modal-text></p>
        <div class="modal-actions">
            <button type="button" class="btn btn-outline" data-modal-cancel>Cancel</button>
            <button type="button" class="btn btn-primary" data-modal-ok>Confirm</button>
        </div>
    </div>
</div>
