@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="pull-right">
            <a class="btn btn-primary" href="{{ route('roles.index') }}"> Back</a>
        </div>
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2> Role: {{ $role->name }}</h2>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Permissions:</strong>
                <div class="text-lg fw-bold d-block">
                    <ul>
                        @if (!empty($rolePermissions))
                            @foreach ($rolePermissions as $v)
                                <li class="">{{ $v->name }}</li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
