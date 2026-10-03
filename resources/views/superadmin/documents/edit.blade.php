@extends('layouts.superadmin')

@section('title', 'Редактирование документа')
@section('page-title', 'Редактирование документа')
@section('page-subtitle', $document->title)

@section('content')
@include('superadmin.documents.form', ['document' => $document, 'submitLabel' => 'Сохранить изменения'])
@endsection
