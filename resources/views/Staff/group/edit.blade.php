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
    <li class="breadcrumbV2">
        {{ $group->name }}
    </li>
    <li class="breadcrumb--active">
        {{ __('common.edit') }}
    </li>
@endsection

@section('page', 'page__staff-group--edit')

@section('main')
    <section class="panelV2" x-data="{ autogroup: {{ Js::from($group->autogroup) }} }">
        <h2 class="panel__heading">Edit Group: {{ $group->name }}</h2>
        <div class="panel__body">
            @include('Staff.group.partials.form', ['group' => $group])
        </div>
    </section>
@endsection
