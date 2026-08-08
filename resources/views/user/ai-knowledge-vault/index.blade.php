@extends('user.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Train AI Assistant') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('user-dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Train AI Assistant') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="alert alert-info py-3">
                {{ __('Upload your hotel information (policies, services, menu, FAQs etc.). This helps the AI to answer customer’s general questionaries.') }}
            </div>
        </div>

        <div class="col-lg-8 mb-4 mx-auto">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">{{ __('Train AI Assistant') }}</div>
                </div>
                <div class="card-body">
                    <form id="ajaxForm" class="vault-upload-form create"
                        action="{{ route('user.ai_knowledge_vault.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label class="mb-0">{{ __('Document File') }}</label>
                            @if (!empty($vaultItem) && !empty($vaultItem->stored_filename))
                                <div class="d-flex align-items-center mt-0">
                                    <a class="file-link mr-2 ml-1"
                                        href="{{ asset($vaultItem->stored_path . '/' . $vaultItem->stored_filename) }}"
                                        target="_blank">
                                        {{ $vaultItem->original_filename }}
                                    </a>

                                    <button type="submit" form="vaultDeleteForm"
                                        class="btn btn-danger btn-sm deleteBtn vaultDeleteForm"
                                        title="{{ __('Delete file') }}" aria-label="{{ __('Delete file') }}">

                                        <i class="fas fa-times"></i>

                                    </button>
                                </div>
                            @endif
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="vaultFile" name="document"
                                    accept=".pdf,.docx,.txt,.md,.csv,.json,.xml,.html,.htm,.log">
                                <label class="custom-file-label" for="vaultFile">{{ __('Choose file') }}</label>
                            </div>
                            <small class="form-text text-muted">
                                {{ __('Supported') }}{{ __(': PDF, DOCX, TXT, MD, CSV, JSON, XML, HTML, LOG.') }}
                            </small>

                            <p class="text-warning mb-0"><strong>{{ __('Important') . ': ' }}</strong>
                                {{ __('Uploading a new file will replace the previous file and update the extracted text below') }}
                            </p>

                            <p class="form-text text-warning mb-0">
                                {{ __('Upload a clean, well-structured text document so the AI can understand context better. The more context you provide, the more tokens will be used.') }}
                            </p>

                            <p class="text-danger mt-1 mb-0 em" id="err_document"></p>
                        </div>

                        <div class="form-group">
                            <label>{{ __('Extracted Text') }}</label>
                            <textarea class="form-control" name="extracted_text" id="extractedText" rows="6"
                                placeholder="{{ __('Extracted text will appear here after uploading the document. You can edit it before submitting.') }}">{{ $vaultItem->extracted_text ?? old('extracted_text') }}</textarea>
                            <p class="form-text text-info mt-1 mb-0">
                            <p class="mb-0 text-info">
                                {{ __('The content of your uploaded file will appear here') }}
                            </p>
                            <strong class="text-secondary">{{ __('What you can do here') . ':' }}</strong>
                            <ul class="mb-0 pl-3 text-warning">
                                <li>
                                    {{ __('You can also type or edit your hotel information here without uploading a file.') }}
                                </li>

                                <li>
                                    {{ __('You can edit the text below and click “Submit” to update the assistant.') }}
                                </li>
                                <li>
                                    {{ __('Any changes you make here will only update the assistant. Your uploaded file will stay unchanged.') }}
                                </li>
                            </ul>

                            </p>
                            <p class="text-danger mt-1 mb-0 em" id="err_extracted_text"></p>
                        </div>


                        <div class="d-flex justify-content-center">
                            <button type="button" id="submitBtn" class="btn btn-primary float-right">
                                {{ __('Submit') }}
                            </button>
                        </div>

                    </form>

                    @if (!empty($vaultItem) && !empty($vaultItem->stored_filename))
                        <form id="vaultDeleteForm" class="deleteForm d-none"
                            action="{{ route('user.ai_knowledge_vault.delete') }}" method="post">
                            @csrf
                            <input type="hidden" name="vault_id" value="{{ $vaultItem->id }}">
                        </form>
                    @endif
                </div>
            </div>
        </div>

    </div>
@endsection
