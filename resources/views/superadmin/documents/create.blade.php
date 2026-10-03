@extends('layouts.superadmin')

@section('title', 'Новый документ')
@section('page-title', 'Создание документа')
@section('page-subtitle', 'Добавление записи в систему документооборота')

@section('content')
@include('superadmin.documents.form', ['document' => null, 'submitLabel' => 'Создать документ'])
@endsection
