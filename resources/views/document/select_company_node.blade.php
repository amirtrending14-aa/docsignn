@php
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
    
    // Подсчет подразделений
    $childrenCount = isset($node->nestedChildren) ? $node->nestedChildren->count() : 0;
@endphp

<div class="org-node">
    <div class="org-card">
        <div class="org-dot"></div>

        {{-- Верх: аватар + бейджи --}}
        <div class="oc-top">
            <div class="oc-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($node->name, 0, 2)) }}</div>
            <div class="oc-badges">
                <span class="oc-lvl">L{{ ($node->depth ?? 0) + 1 }}</span>
                <span class="oc-status {{ strtolower($node->status ?? 'active') == 'active' ? 'on' : 'off' }}">
                    <span class="oc-status-dot"></span>
                    <span>{{ strtolower($node->status ?? 'active') == 'active' ? 'Active' : 'Inactive' }}</span>
                </span>
            </div>
        </div>

        {{-- Имя --}}
        <h3 class="oc-name">{{ $node->name }}</h3>

        {{-- Тип (Красная плашка как на фото) --}}
        <div class="oc-type-badge">{{ $typeLabel }}</div>

        {{-- Если это корень, показываем золотую плашку ROOT --}}
        @if(!$node->parent_id)
            <div class="oc-root-badge">
                <i class="bi bi-star-fill"></i> ROOT
            </div>
        @endif

        {{-- Область --}}
        <div class="oc-info-row">
            <i class="bi bi-geo-alt-fill"></i>
            <span class="oc-info-text">{{ $node->region->name_ru ?? '—' }}</span>
        </div>
        
        {{-- Город --}}
        <div class="oc-info-row">
            <i class="bi bi-building"></i>
            <span class="oc-info-text">{{ $node->city->name_ru ?? '—' }}</span>
        </div>

        {{-- Футер --}}
        <div class="oc-footer">
            {{-- Плашка подразделений (зеленая) --}}
            <div class="oc-subdivs">
                <i class="bi bi-diagram-3"></i> Subdivisions: <span>{{ $childrenCount }}</span>
            </div>

            {{-- Кнопка выбора --}}
            <div class="oc-actions">
                <button type="button" class="oc-act select" onclick="selectCompanyUsers({{ $node->id }})" title="Выбрать сотрудников">
                    <i class="bi bi-person-check"></i> Выбрать
                </button>
            </div>
        </div>
    </div>

    {{-- Рекурсия: подразделения этого узла --}}
    @if(isset($node->nestedChildren) && $node->nestedChildren->count() > 0)
        <div class="org-children">
            @foreach($node->nestedChildren as $child)
                @include('document.select_company_node', ['node' => $child])
            @endforeach
        </div>
    @endif
</div>