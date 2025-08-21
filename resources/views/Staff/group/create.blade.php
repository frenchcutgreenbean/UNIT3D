@extends('layout.with-main')

@section('breadcrumbs')
    <li class="breadcrumbV2">
        <a href="{{ route('staff.dashboard.index') }}" class="breadcrumb__link">
            {{ __('staff.staff-dashboard') }}
        </a>
    </li>
    <li class="breadcrumbV2">
        <a href="{{ route('staff.groups.index') }}" class="breadcrumb__link">
            {{ __('staff.groups') }}
        </a>
    </li>
    <li class="breadcrumb--active">
        {{ __('common.new-adj') }}
    </li>
@endsection

@section('page', 'page__staff-group--create')

@section('main')
    <section class="panelV2" x-data="{ autogroup: false }">
        <h2 class="panel__heading">Add New Group</h2>
        <div class="panel__body">
            @include('Staff.group.partials.form', ['group' => new \App\Models\Group])
        </div>
    </section>
@endsection
