@php
    $canManage = auth()->user()->canManageCompany($node);

    // Маппинг машинных ключей типа → русские названия
    $typeLabels = [
        'ministry'           => 'Правительство и министерства',
        'local_government'   => 'Местное самоуправление',
        'law_enforcement'    => 'Силовые структуры',
        'special_agency'     => 'Специализированные агентства',
        'education'          => 'Образование',
        'healthcare'         => 'Здравоохранение',
        'social_protection'  => 'Социальная защита',
        'bank'               => 'Финансы',
        'business_services'  => 'Деловые услуги',
        'it_development'     => 'IT и разработка',
        'retail'             => 'Торговля',
        'catering'           => 'Общепит',
        'manufacturing'      => 'Промышленность',
        'construction'       => 'Строительство',
        'household_services' => 'Бытовые услуги',
        'hospitality'        => 'Гостиничный бизнес',
        'sport_leisure'      => 'Спорт и досуг',
        'utilities'          => 'ЖКХ',
        'transport'          => 'Транспорт',
        'communication'      => 'Связь',
    ];
    $typeLabel = $typeLabels[$node->type] ?? ($node->type ?? '—');
@endphp

<div class="org-node">
    <div class="org-card">
        <div class="org-dot"></div>

        {{-- Верх: аватар + бейджи --}}
        <div class="oc-top">
            <div class="oc-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($node->name, 0, 2)) }}</div>
            <div class="oc-badges">
                <span class="oc-lvl">L{{ ($node->depth ?? 0) + 1 }}</span>
                <span class="oc-status {{ strtolower($node->status) == 'active' ? 'on' : 'off' }}">
                    <span class="oc-status-dot"></span>
                    <span data-i18n="{{ strtolower($node->status) == 'active' ? 'oc_active' : 'oc_inactive' }}">{{ strtolower($node->status) == 'active' ? 'Активна' : 'Неактивна' }}</span>
                </span>
            </div>
        </div>

        {{-- Имя --}}
        <h3 class="oc-name"><a href="{{ route('companies.show', $node) }}">{{ $node->name }}</a></h3>

        {{-- Тип — русское название --}}
        <span class="oc-type">{{ $typeLabel }}</span>

        {{-- Корень тег --}}
        @if($node->isRoot())
            <div class="oc-root-tag" data-i18n="oc_root">★ Корень</div>
        @endif

        {{-- Область --}}
        <div class="oc-city">
            <i class="bi bi-geo-alt-fill"></i>
            <span>{{ $node->region->name_ru ?? '—' }}</span>
        </div>
        {{-- Город --}}
        <div class="oc-city" style="margin-top:-3px;">
            <i class="bi bi-building"></i>
            <span>{{ $node->city->name_ru ?? '—' }}</span>
        </div>

        {{-- Родитель --}}
        @if(!$node->isRoot() && $node->parent)
            <div class="oc-parent">
                <i class="bi bi-arrow-return-right"></i>
                <span data-i18n="oc_parent">Родитель:</span>
                <strong>{{ $node->parent->name }}</strong>
            </div>
        @endif

        {{-- ✅ Количество подразделений --}}
        @if($node->nestedChildren && $node->nestedChildren->count() > 0)
            <div class="oc-children-badge">
                <i class="bi bi-diagram-3-fill"></i>
                <span data-i18n="oc_children">Подразделений:</span>
                <strong>{{ $node->nestedChildren->count() }}</strong>
            </div>
        @endif

        {{-- Кнопки --}}
        @if($canManage)
            <div class="oc-actions">
                <a href="{{ route('companies.show', $node) }}" class="oc-act view" data-i18n-title="oc_view" title="Просмотр"><i class="bi bi-eye-fill"></i></a>
                <a href="{{ route('companies.create', ['parent' => $node->id]) }}" class="oc-act add" data-i18n-title="oc_add_child" title="Подразделение"><i class="bi bi-plus-lg"></i></a>
                <a href="{{ route('companies.edit', $node) }}" class="oc-act edit" data-i18n-title="oc_edit" title="Редактировать"><i class="bi bi-pencil-fill"></i></a>
                @if(!$node->isRoot() && $node->nestedChildren->count() === 0)
                    <form action="{{ route('companies.destroy', $node) }}" method="POST" style="flex:1;margin:0;" data-confirm-i18n="oc_confirm_delete">
                        @csrf @method('DELETE')
                        <button type="submit" class="oc-act del" style="width:100%;border:none;background:none;" data-i18n-title="oc_delete" title="Удалить"><i class="bi bi-trash-fill"></i></button>
                    </form>
                @endif
            </div>
        @endif
    </div>

    {{-- Рекурсия: подразделения этого узла --}}
    @if($node->nestedChildren && $node->nestedChildren->count() > 0)
        <div class="org-children">
            @foreach($node->nestedChildren as $child)
                @include('company._tree_node', ['node' => $child])
            @endforeach
        </div>
    @endif
</div>