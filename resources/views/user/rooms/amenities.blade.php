@extends('user.layout')

{{-- this style will be applied when the direction of language is right-to-left --}}
@includeIf('user.partials.rtl-style')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Amenities') }}</h4>
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
        <a href="#">{{ __('Rooms Management') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Settings') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Amenities') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-lg-4">
              <div class="card-title d-inline-block">{{ __('Room Amenities') }}</div>
            </div>

            <div class="col-lg-3">
              @includeIf('user.partials.languages')
            </div>

            <div class="col-lg-4 offset-lg-1 mt-2 mt-lg-0">
              <a href="#" data-toggle="modal" data-target="#createModal"
                class="btn btn-primary  float-right"><i class="fas fa-plus"></i>
                {{ __('Add Amenity') }}</a>

              <button class="btn btn-danger  float-right mr-2 d-none bulk-delete"
                data-href="{{ route('tenant.rooms_management.bulk_delete_amenity') }}"><i
                  class="flaticon-interface-5"></i> {{ __('Delete') }}</button>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              @if (count($amenities) == 0)
                <h3 class="text-center">{{ __('NO ROOM AMENITY FOUND!') }}</h3>
              @else
                <div class="table-responsive">
                  <table class="table table-striped mt-3" id="basic-datatables">
                    <thead>
                      <tr>
                        <th scope="col">
                          <input type="checkbox" class="bulk-check" data-val="all">
                        </th>
                        <th scope="col">{{ __('Name') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col">{{ __('Serial Number') }}</th>
                        <th scope="col">{{ __('Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($amenities as $amenity)
                        <tr>
                          <td>
                            <input type="checkbox" class="bulk-check" data-val="{{ $amenity->id }}">
                          </td>
                          <td>
                            {{ strlen($amenity->name) > 100
                                ? convertUtf8(substr($amenity->name, 0, 100)) . '...'
                                : convertUtf8($amenity->name) }}
                          </td>
                          <td>
                            @if ($amenity->status == 1)
                              <h2 class="d-inline-block"><span class="badge badge-success">{{ __('Active') }}</span>
                              </h2>
                            @else
                              <h2 class="d-inline-block"><span class="badge badge-danger">{{ __('Deactive') }}</span>
                              </h2>
                            @endif
                          </td>
                          <td>{{ $amenity->serial_number }}</td>
                          <td>
                            <a class="btn btn-secondary btn-sm mr-1 mb-1"
                              href="{{ route('tenant.rooms_management.edit_amenity', ['id' => $amenity->id, 'language' => request()->language]) }}">
                              <span class="btn-label">
                                <i class="fas fa-edit"></i> {{ __('Edit') }}
                              </span>
                            </a>

                            <form class="deleteForm d-inline-block"
                              action="{{ route('tenant.rooms_management.delete_amenity') }}" method="post">
                              @csrf
                              <input type="hidden" name="amenity_id" value="{{ $amenity->id }}">

                              <button type="submit" class="btn btn-danger btn-sm deleteBtn mb-1">
                                <span class="btn-label">
                                  <i class="fas fa-trash"></i> {{ __('Delete') }}
                                </span>
                              </button>
                            </form>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @endif
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  {{-- create modal --}}
  @include('user.rooms.create_amenity')

@endsection
