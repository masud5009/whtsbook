    {{-- thumbnail --}}
    <p class="d-none" id="blob_image"></p>
    <div class="modal fade" id="thumbnail-image-modal" tabindex="-1" role="dialog"
        aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h2>{{ __('Thumbnail') }}*</h2>
                    <button role="button" class="close btn btn-secondary mr-2 destroy-cropper d-none text-white"
                        data-dismiss="modal" aria-label="Close">
                        {{ __('Crop') }}
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <div class="thumb-previews" style="background: none;">
                            <img src="{{ asset('assets/admin/img/noimage.jpg') }}"
                                data-no_image="{{ asset('assets/admin/img/noimage.jpg') }}" alt="..."
                                class="uploaded-thumbnail-img img-thumbnail" id="image">
                        </div>
                        <div class="mt-3">
                            <div role="button" class="btn btn-primary btn-sm fw-bold upload-btn">
                                {{ __('Choose Image') }}
                                <input type="file" class="thumbnail-input" name="thumbnail_image" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- thumbnail end --}}
