@extends('layout.with-main')
@section('title')
    <title>Edit Bet - {{ config('other.title') }}</title>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb--active">
        Edit Bet
    </li>
@endsection
@section('page', 'page__bets--index')

@section('main')
    @include('bets.partials.form', [
        'action' => route('bets.update', $bet->id),
        'method' => 'PUT',
        'buttonText' => 'Update Bet',
        'bet' => $bet,
        'titleText' => 'Edit'
    ])
@endsection
