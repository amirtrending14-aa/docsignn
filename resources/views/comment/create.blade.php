{{--@extends('layouts.app')--}}

{{--@section('content')--}}
{{--    <div class="container">--}}

{{--        <h2 class="mb-3">Добавить комментарий</h2>--}}

{{--        --}}{{-- ошибки --}}
{{--        @if ($errors->any())--}}
{{--            <div class="alert alert-danger">--}}
{{--                <ul>--}}
{{--                    @foreach ($errors->all() as $error)--}}
{{--                        <li>{{ $error }}</li>--}}
{{--                    @endforeach--}}
{{--                </ul>--}}
{{--            </div>--}}
{{--        @endif--}}

{{--        <div class="card">--}}
{{--            <div class="card-body">--}}

{{--                <form action="{{ route('comments.store') }}" method="POST">--}}
{{--                    @csrf--}}

{{--                    --}}{{-- документ --}}
{{--                    <div class="mb-3">--}}
{{--                        <label>Документ</label>--}}
{{--                        <select name="document_id" class="form-control" required>--}}
{{--                            @foreach($documents as $doc)--}}
{{--                                <option value="{{ $doc->id }}"--}}
{{--                                    {{ old('document_id') == $doc->id ? 'selected' : '' }}>--}}
{{--                                    {{ $doc->title }}--}}
{{--                                </option>--}}
{{--                            @endforeach--}}
{{--                        </select>--}}
{{--                    </div>--}}

{{--                    --}}{{-- пользователь (временно, без Auth) --}}
{{--                    <div class="mb-3">--}}
{{--                        <label>Пользователь</label>--}}
{{--                        <select name="user_id" class="form-control" required>--}}
{{--                            @foreach($users as $user)--}}
{{--                                <option value="{{ $user->id }}"--}}
{{--                                    {{ old('user_id') == $user->id ? 'selected' : '' }}>--}}
{{--                                    {{ $user->name }}--}}
{{--                                </option>--}}
{{--                            @endforeach--}}
{{--                        </select>--}}
{{--                    </div>--}}

{{--                    --}}{{-- комментарий --}}
{{--                    <div class="mb-3">--}}
{{--                        <label>Комментарий</label>--}}
{{--                        <textarea name="comment"--}}
{{--                                  class="form-control"--}}
{{--                                  rows="4"--}}
{{--                                  required>{{ old('comment') }}</textarea>--}}
{{--                    </div>--}}

{{--                    --}}{{-- кнопки --}}
{{--                    <button class="btn btn-success" type="submit">--}}
{{--                        Сохранить--}}
{{--                    </button>--}}
{{--                    <a href="{{ route('comments.index', ['documentId' => $document->id]) }}">--}}
{{--                                                Назад--}}
{{--                    </a>--}}

{{--                </form>--}}

{{--            </div>--}}
{{--        </div>--}}

{{--    </div>--}}
{{--@endsection--}}
