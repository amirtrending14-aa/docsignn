@props(['data', 'id', 'color' => '139, 92, 246'])

@php
$max = max($data) ?: 1;
$min = min($data);
$height = 40;
$width = 200;
$step = $width / (count($data) - 1);

$points = [];
foreach ($data as $i => $value) {
$x = $i * $step;
$y = $height - (($value - $min) / ($max - $min ?: 1)) * ($height - 5) - 2;
$points[] = round($x, 1) . ',' . round($y, 1);
}
$pathLine = 'M' . implode(' L', $points);
$pathFill = $pathLine . " L{$width},{$height} L0,{$height} Z";
@endphp

<style>
    .sparkline-svg-{{ $id }} {
        width: 100%;
        height: 40px;
        display: block;
    }

    /* Планшеты и маленькие ноутбуки (до 768px) */
    @media (max-width: 768px) {
        .sparkline-svg-{{ $id }} {
            height: 36px;
        }
    }

    /* Большие телефоны (до 576px) */
    @media (max-width: 576px) {
        .sparkline-svg-{{ $id }} {
            height: 32px;
        }
    }

    /* Телефоны (до 480px) */
    @media (max-width: 480px) {
        .sparkline-svg-{{ $id }} {
            height: 28px;
        }
    }

    /* Очень маленькие телефоны (до 380px) */
    @media (max-width: 380px) {
        .sparkline-svg-{{ $id }} {
            height: 24px;
        }
    }
</style>

<svg class="sparkline-svg-{{ $id }}" viewBox="0 0 {{ $width }} {{ $height }}" preserveAspectRatio="none">
    <defs>
        <linearGradient id="grad-{{ $id }}" x1="0" x2="0" y1="0" y2="1">
            <stop offset="0" stop-color="rgba({{ $color }}, 0.6)"/>
            <stop offset="1" stop-color="rgba({{ $color }}, 0)"/>
        </linearGradient>
    </defs>
    <path d="{{ $pathFill }}" fill="url(#grad-{{ $id }})"/>
    <path d="{{ $pathLine }}" fill="none" stroke="rgba({{ $color }}, 0.9)" stroke-width="1.5"/>
</svg>