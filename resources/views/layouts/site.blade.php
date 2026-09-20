<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DocSign</title>
 
<link rel="icon" type="image/png" href="{{ asset('img/dss.png') }}">
<link rel="apple-touch-icon" href="{{ asset('img/dss.png') }}">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/inter@5.0.16/index.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<script>

tailwind.config = {
theme: {
extend: {
fontFamily: {
sans: ['Inter', 'sans-serif'],
},
colors: {
ink: {
950: '#050505',
900: '#0a0a0a',
850: '#121212',
800: '#1a1a1a',
700: '#2a2a2a',
600: '#3a3a3a',
}
}
}
}
}
</script>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
html, body {
font-family: 'Inter', sans-serif;
background: #050505;
color: #e5e5e5;
overflow-x: hidden;
-webkit-font-smoothing: antialiased;
}
#three-bg {
position: fixed;
top: 0; left: 0;
width: 100%; height: 100%;
z-index: 0;
pointer-events: none;
}
.content-wrap {
position: relative;
z-index: 1;
}
/* Colorful Blobs Background */
.bg-blob {
position: fixed;
border-radius: 50%;
filter: blur(120px);
opacity: 0.15;
pointer-events: none;
z-index: 0;
animation: blobFloat 20s ease-in-out infinite;
}
.blob-1 {
top: -200px;
left: -200px;
width: 600px;
height: 600px;
background: radial-gradient(circle, #3b82f6 0%, transparent 70%);
animation-delay: 0s;
}
.blob-2 {
bottom: -200px;
right: -200px;
width: 700px;
height: 700px;
background: radial-gradient(circle, #8b5cf6 0%, transparent 70%);
animation-delay: 5s;
}
.blob-3 {
top: 40%;
left: 60%;
width: 500px;
height: 500px;
background: radial-gradient(circle, #ec4899 0%, transparent 70%);
animation-delay: 10s;
}
.blob-4 {
top: 60%;
left: 20%;
width: 400px;
height: 400px;
background: radial-gradient(circle, #10b981 0%, transparent 70%);
animation-delay: 15s;
}
@keyframes blobFloat {
0%, 100% { transform: translate(0, 0) scale(1); }
33% { transform: translate(50px, -50px) scale(1.1); }
66% { transform: translate(-30px, 30px) scale(0.95); }
}
.gradient-text {
background: linear-gradient(135deg, #ffffff 0%, #a0a0a0 50%, #ffffff 100%);
-webkit-background-clip: text;
-webkit-text-fill-color: transparent;
background-clip: text;
}
.gradient-accent {
background: linear-gradient(135deg, #3b82f6, #8b5cf6, #ec4899);
-webkit-background-clip: text;
-webkit-text-fill-color: transparent;
background-clip: text;
}
.gradient-ai {
background: linear-gradient(135deg, #06b6d4, #8b5cf6, #ec4899);
-webkit-background-clip: text;
-webkit-text-fill-color: transparent;
background-clip: text;
}
.glass {
background: rgba(20, 20, 20, 0.55);
backdrop-filter: blur(24px) saturate(180%);
-webkit-backdrop-filter: blur(24px) saturate(180%);
border: 1px solid rgba(255, 255, 255, 0.06);
}
.glass-strong {
background: rgba(10, 10, 10, 0.85);
backdrop-filter: blur(30px) saturate(180%);
-webkit-backdrop-filter: blur(30px) saturate(180%);
border: 1px solid rgba(255, 255, 255, 0.08);
}
.card-3d {
position: relative;
transform-style: preserve-3d;
transition: transform 0.6s cubic-bezier(0.23, 1, 0.32, 1), box-shadow 0.6s ease;
will-change: transform;
}
.card-3d:hover {
box-shadow: 0 30px 60px -15px rgba(0,0,0,0.7), 0 0 40px rgba(255,255,255,0.05);
}
.card-3d .card-inner {
transform: translateZ(0);
transition: transform 0.6s cubic-bezier(0.23, 1, 0.32, 1);
}
.card-3d:hover .card-inner {
transform: translateZ(30px);
}
.card-shine {
position: absolute;
inset: 0;
border-radius: inherit;
pointer-events: none;
opacity: 0;
transition: opacity 0.4s ease;
background: radial-gradient(600px circle at var(--mx, 50%) var(--my, 50%), rgba(255,255,255,0.08), transparent 40%);
z-index: 2;
}
.card-3d:hover .card-shine { opacity: 1; }
.card-border {
position: absolute;
inset: 0;
border-radius: inherit;
padding: 1px;
background: linear-gradient(135deg, rgba(255,255,255,0.15), transparent 40%, transparent 60%, rgba(255,255,255,0.08));
-webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
-webkit-mask-composite: xor;
mask-composite: exclude;
pointer-events: none;
}
/* Colorful Card Borders on Hover */
.card-blue:hover .card-border {
background: linear-gradient(135deg, #3b82f6, transparent 40%, transparent 60%, #3b82f6);
}
.card-purple:hover .card-border {
background: linear-gradient(135deg, #8b5cf6, transparent 40%, transparent 60%, #8b5cf6);
}
.card-green:hover .card-border {
background: linear-gradient(135deg, #10b981, transparent 40%, transparent 60%, #10b981);
}
.card-orange:hover .card-border {
background: linear-gradient(135deg, #f59e0b, transparent 40%, transparent 60%, #f59e0b);
}
.card-pink:hover .card-border {
background: linear-gradient(135deg, #ec4899, transparent 40%, transparent 60%, #ec4899);
}
.card-indigo:hover .card-border {
background: linear-gradient(135deg, #6366f1, transparent 40%, transparent 60%, #6366f1);
}
.card-cyan:hover .card-border {
background: linear-gradient(135deg, #06b6d4, transparent 40%, transparent 60%, #06b6d4);
}
.card-red:hover .card-border {
background: linear-gradient(135deg, #ef4444, transparent 40%, transparent 60%, #ef4444);
}
.card-ai:hover .card-border {
background: linear-gradient(135deg, #06b6d4, transparent 40%, transparent 60%, #8b5cf6);
}
.btn-primary {
background: linear-gradient(135deg, #fff, #ccc);
color: #000;
transition: all 0.3s ease;
position: relative;
overflow: hidden;
}
.btn-primary:hover {
transform: translateY(-2px);
box-shadow: 0 15px 40px rgba(255, 255, 255, 0.15);
}
.btn-outline {
background: transparent;
border: 1px solid rgba(255, 255, 255, 0.15);
transition: all 0.3s ease;
}
.btn-outline:hover {
background: rgba(255, 255, 255, 0.05);
border-color: rgba(255, 255, 255, 0.3);
}
/* ВАЖНО: скрываем блоки до появления только если JS работает */
.js .scroll-reveal {
opacity: 0;
transform: translateY(40px);
transition: all 0.9s cubic-bezier(0.16, 1, 0.3, 1);
}
.js .scroll-reveal.revealed {
opacity: 1;
transform: translateY(0);
}
.nav-link {
position: relative;
transition: color 0.3s ease;
}
.nav-link::after {
content: '';
position: absolute;
bottom: -4px;
left: 0;
width: 0;
height: 1px;
background: linear-gradient(90deg, #3b82f6, #8b5cf6);
transition: width 0.3s ease;
}
.nav-link:hover::after { width: 100%; }
.nav-link:hover { color: #fff; }
.counter { font-variant-numeric: tabular-nums; }
.line-glow {
height: 1px;
background: linear-gradient(90deg, transparent, #3b82f6, #8b5cf6, transparent);
}
.bg-grid {
background-image:
linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
background-size: 60px 60px;
}
::-webkit-scrollbar { width: 8px; }
::-webkit-scrollbar-track { background: #050505; }
::-webkit-scrollbar-thumb { background: #2a2a2a; border-radius: 4px; }
::-webkit-scrollbar-thumb:hover { background: #444; }
/* Language Select Styles */
.lang-select-wrap {
position: relative;
display: inline-flex;
align-items: center;
}
.lang-select {
appearance: none;
-webkit-appearance: none;
-moz-appearance: none;
background: rgba(20, 20, 20, 0.55);
backdrop-filter: blur(24px) saturate(180%);
-webkit-backdrop-filter: blur(24px) saturate(180%);
border: 1px solid rgba(255, 255, 255, 0.08);
color: #e5e5e5;
padding: 6px 28px 6px 10px;
border-radius: 8px;
font-size: 11px;
font-weight: 600;
letter-spacing: 0.05em;
cursor: pointer;
outline: none;
transition: all 0.3s ease;
font-family: 'Inter', sans-serif;
}
.lang-select:hover {
border-color: rgba(59, 130, 246, 0.4);
background: rgba(59, 130, 246, 0.08);
}
.lang-select:focus {
border-color: rgba(59, 130, 246, 0.6);
box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}
.lang-select option {
background: #121212;
color: #e5e5e5;
padding: 8px;
}
.lang-select-arrow {
position: absolute;
right: 8px;
top: 50%;
transform: translateY(-50%);
pointer-events: none;
color: #3b82f6;
transition: transform 0.3s ease;
}
.lang-select:focus ~ .lang-select-arrow {
transform: translateY(-50%) rotate(180deg);
}
.stat-num {
font-size: 2.25rem;
font-weight: 800;
letter-spacing: -0.04em;
line-height: 1;
}
@media (max-width: 768px) {
.hero-title { font-size: 2.25rem !important; }
.stat-num { font-size: 1.75rem; }
}
.superadmin-card {
background: linear-gradient(135deg, rgba(30,30,30,0.9), rgba(15,15,15,0.9));
border: 1px solid rgba(255,255,255,0.1);
position: relative;
overflow: hidden;
}
.superadmin-card::before {
content: '';
position: absolute;
top: -50%;
left: -50%;
width: 200%;
height: 200%;
background: conic-gradient(from 0deg, transparent, rgba(59,130,246,0.1), rgba(139,92,246,0.1), transparent 30%);
animation: rotate 8s linear infinite;
}
@keyframes rotate {
from { transform: rotate(0deg); }
to { transform: rotate(360deg); }
}
.superadmin-card > * { position: relative; z-index: 1; }
.pulse-dot {
width: 8px; height: 8px; border-radius: 50%;
background: #10b981;
box-shadow: 0 0 12px #10b981;
animation: pulseDot 2s ease-in-out infinite;
}
@keyframes pulseDot {
0%, 100% { opacity: 1; transform: scale(1); }
50% { opacity: 0.5; transform: scale(1.3); }
}
/* Colorful Icon Boxes */
.icon-blue {
background: linear-gradient(135deg, rgba(59,130,246,0.2), rgba(59,130,246,0.05));
border: 1px solid rgba(59,130,246,0.3);
box-shadow: 0 0 20px rgba(59,130,246,0.2);
}
.icon-purple {
background: linear-gradient(135deg, rgba(139,92,246,0.2), rgba(139,92,246,0.05));
border: 1px solid rgba(139,92,246,0.3);
box-shadow: 0 0 20px rgba(139,92,246,0.2);
}
.icon-green {
background: linear-gradient(135deg, rgba(16,185,129,0.2), rgba(16,185,129,0.05));
border: 1px solid rgba(16,185,129,0.3);
box-shadow: 0 0 20px rgba(16,185,129,0.2);
}
.icon-orange {
background: linear-gradient(135deg, rgba(245,158,11,0.2), rgba(245,158,11,0.05));
border: 1px solid rgba(245,158,11,0.3);
box-shadow: 0 0 20px rgba(245,158,11,0.2);
}
.icon-pink {
background: linear-gradient(135deg, rgba(236,72,153,0.2), rgba(236,72,153,0.05));
border: 1px solid rgba(236,72,153,0.3);
box-shadow: 0 0 20px rgba(236,72,153,0.2);
}
.icon-indigo {
background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(99,102,241,0.05));
border: 1px solid rgba(99,102,241,0.3);
box-shadow: 0 0 20px rgba(99,102,241,0.2);
}
.icon-cyan {
background: linear-gradient(135deg, rgba(6,182,212,0.2), rgba(6,182,212,0.05));
border: 1px solid rgba(6,182,212,0.3);
box-shadow: 0 0 20px rgba(6,182,212,0.2);
}
.icon-red {
background: linear-gradient(135deg, rgba(239,68,68,0.2), rgba(239,68,68,0.05));
border: 1px solid rgba(239,68,68,0.3);
box-shadow: 0 0 20px rgba(239,68,68,0.2);
}
.icon-ai {
background: linear-gradient(135deg, rgba(6,182,212,0.2), rgba(139,92,246,0.1));
border: 1px solid rgba(6,182,212,0.35);
box-shadow: 0 0 25px rgba(6,182,212,0.25), 0 0 50px rgba(139,92,246,0.1);
}
.feature-icon-box {
background: linear-gradient(135deg, rgba(255,255,255,0.08), rgba(255,255,255,0.02));
border: 1px solid rgba(255,255,255,0.1);
}
.mini-chart-bar {
transition: height 1s cubic-bezier(0.23, 1, 0.32, 1);
}
/* Colorful Stat Cards */
.stat-card-blue {
background: linear-gradient(135deg, rgba(59,130,246,0.08), rgba(59,130,246,0.02));
border: 1px solid rgba(59,130,246,0.15);
}
.stat-card-purple {
background: linear-gradient(135deg, rgba(139,92,246,0.08), rgba(139,92,246,0.02));
border: 1px solid rgba(139,92,246,0.15);
}
.stat-card-green {
background: linear-gradient(135deg, rgba(16,185,129,0.08), rgba(16,185,129,0.02));
border: 1px solid rgba(16,185,129,0.15);
}
.stat-card-orange {
background: linear-gradient(135deg, rgba(245,158,11,0.08), rgba(245,158,11,0.02));
border: 1px solid rgba(245,158,11,0.15);
}
.stat-card-ai {
background: linear-gradient(135deg, rgba(6,182,212,0.1), rgba(139,92,246,0.05));
border: 1px solid rgba(6,182,212,0.2);
}
/* Donut Chart Animation */
.donut-segment {
transition: stroke-dasharray 1.5s cubic-bezier(0.23, 1, 0.32, 1), stroke-dashoffset 1.5s cubic-bezier(0.23, 1, 0.32, 1);
stroke-dasharray: 0 100;
stroke-dashoffset: 0;
}
.donut-segment.animated {}
.donut-center-text {
animation: fadeInScale 0.8s ease-out 0.5s both;
}
@keyframes fadeInScale {
from { opacity: 0; transform: scale(0.8); }
to { opacity: 1; transform: scale(1); }
}
.legend-item {
transition: transform 0.3s ease, opacity 0.3s ease;
}
.legend-item:hover {
transform: translateX(4px);
opacity: 0.8;
}
.donut-header-bar {
background: linear-gradient(90deg, rgba(30,30,30,0.8), rgba(40,40,40,0.6));
border: 1px solid rgba(255,255,255,0.06);
}
/* ===== FACE ID SECTION ===== */
.faceid-perspective { perspective: 1200px; }
.faceid-tilt {
transform-style: preserve-3d;
transition: transform 0.25s ease-out;
will-change: transform;
}
.faceid-layer-frame { transform: translateZ(36px); }
.faceid-layer-status { transform: translateZ(20px); }
.faceid-layer-chip { transform: translateZ(60px); }
.faceid-frame-corner {
position: absolute;
width: 42px;
height: 42px;
z-index: 4;
transition: border-color .6s ease, filter .6s ease;
}
.corner-tl { top: -8px; left: -8px; border-top: 3px solid #3b82f6; border-left: 3px solid #3b82f6; border-top-left-radius: 18px; filter: drop-shadow(0 0 6px rgba(59,130,246,.55)); }
.corner-tr { top: -8px; right: -8px; border-top: 3px solid #3b82f6; border-right: 3px solid #3b82f6; border-top-right-radius: 18px; filter: drop-shadow(0 0 6px rgba(59,130,246,.55)); }
.corner-bl { bottom: -8px; left: -8px; border-bottom: 3px solid #3b82f6; border-left: 3px solid #3b82f6; border-bottom-left-radius: 18px; filter: drop-shadow(0 0 6px rgba(59,130,246,.55)); }
.corner-br { bottom: -8px; right: -8px; border-bottom: 3px solid #3b82f6; border-right: 3px solid #3b82f6; border-bottom-right-radius: 18px; filter: drop-shadow(0 0 6px rgba(59,130,246,.55)); }
.faceid-verified .corner-tl, .faceid-verified .corner-tr,
.faceid-verified .corner-bl, .faceid-verified .corner-br {
border-color: #10b981 !important;
filter: drop-shadow(0 0 8px rgba(16,185,129,.6));
}
.scan-line {
position: absolute;
left: 4%; right: 4%; top: 10%;
height: 2px;
z-index: 5;
border-radius: 2px;
background: linear-gradient(90deg, transparent, #3b82f6 20%, #8b5cf6 50%, #3b82f6 80%, transparent);
box-shadow: 0 0 16px rgba(59,130,246,.8), 0 0 40px rgba(139,92,246,.35);
animation: faceScan 3.4s ease-in-out infinite;
transition: opacity .5s ease;
}
@keyframes faceScan {
0%   { top: 8%;  opacity: .25; }
10%  { opacity: 1; }
50%  { top: 88%; opacity: 1; }
90%  { opacity: 1; }
100% { top: 8%;  opacity: .25; }
}
.faceid-verified .scan-line { opacity: 0; animation-play-state: paused; }
.fd { transition: fill .6s ease, opacity .6s ease; }
.fd-main { fill: #7dd3fc; opacity: .95; }
.fd-soft { fill: #3b82f6; opacity: .22; }
.faceid-verified .fd-main { fill: #6ee7b7; }
.faceid-verified .fd-soft { fill: #10b981; opacity: .16; }
.face-status {
display: inline-flex;
align-items: center;
gap: 8px;
padding: 8px 16px;
border-radius: 999px;
background: rgba(59,130,246,.08);
border: 1px solid rgba(59,130,246,.25);
color: #93c5fd;
font-size: 12px;
font-weight: 600;
transition: all .5s ease;
}
.face-status-ok {
background: rgba(16,185,129,.1);
border-color: rgba(16,185,129,.35);
color: #6ee7b7;
}
.face-status-dot {
width: 7px; height: 7px; border-radius: 50%;
background: #3b82f6;
box-shadow: 0 0 10px #3b82f6;
animation: pulseDot 1.6s ease-in-out infinite;
flex-shrink: 0;
}
.face-check {
position: absolute;
top: 8px; right: 8px;
z-index: 6;
width: 40px; height: 40px;
border-radius: 999px;
background: linear-gradient(135deg, #10b981, #059669);
display: flex;
align-items: center;
justify-content: center;
box-shadow: 0 8px 24px rgba(16,185,129,.45);
opacity: 0;
transform: scale(.4) rotate(-30deg);
transition: all .5s cubic-bezier(.34,1.56,.64,1);
}
.faceid-verified .face-check { opacity: 1; transform: scale(1) rotate(0deg); }
/* ===== GEOGRAPHY MAP ===== */
.map-ping {
transform-box: fill-box;
transform-origin: center;
animation: mapPing 2.6s ease-out infinite;
}
@keyframes mapPing {
0% { transform: scale(.4); opacity: .9; }
100% { transform: scale(2.6); opacity: 0; }
}
.route-line {
stroke-dasharray: 3 7;
animation: routeDash 2.8s linear infinite;
}
@keyframes routeDash { to { stroke-dashoffset: -40; } }
.map-label {
font-size: 9px;
fill: #8b9bb4;
font-weight: 600;
font-family: 'Inter', sans-serif;
}
.map-label-capital {
font-size: 11px;
fill: #e2e8f0;
font-weight: 800;
font-family: 'Inter', sans-serif;
}
.geo-chip {
transition: all .3s ease;
}
.geo-chip:hover {
border-color: rgba(59,130,246,.5);
background: rgba(59,130,246,.08);
transform: translateY(-2px);
}
.region-card { transition: transform .4s cubic-bezier(.23,1,.32,1), border-color .3s ease, background .3s ease; }
.region-card:hover { transform: translateX(6px); border-color: rgba(59,130,246,.35); }
/* ===== HERO ARROWS (белые стрелки) ===== */
.hero-arrows span {
color: #ffffff;
text-shadow: 0 0 18px rgba(255,255,255,0.45), 0 0 40px rgba(255,255,255,0.2);
animation: arrowFloat 1.8s ease-in-out infinite;
}
.hero-arrows span:nth-child(2) { animation-delay: .25s; }
.hero-arrows span:nth-child(3) { animation-delay: .5s; }
@keyframes arrowFloat {
0%, 100% { transform: translateY(0); }
50% { transform: translateY(10px); }
}

/* ===== AI / RAG SECTION ===== */
.ai-section-bg {
position: relative;
overflow: hidden;
}
.ai-section-bg::before {
content: '';
position: absolute;
inset: 0;
background:
radial-gradient(ellipse at 20% 30%, rgba(6,182,212,0.12) 0%, transparent 55%),
radial-gradient(ellipse at 80% 70%, rgba(139,92,246,0.1) 0%, transparent 55%);
pointer-events: none;
z-index: 0;
}
.ai-brain-wrap {
position: relative;
width: 320px;
height: 320px;
margin: 0 auto;
}
.ai-brain-core {
position: absolute;
inset: 0;
display: flex;
align-items: center;
justify-content: center;
}
.ai-brain-core svg {
width: 180px;
height: 180px;
filter: drop-shadow(0 0 30px rgba(6,182,212,0.5));
}
.ai-orbit {
position: absolute;
inset: 0;
border-radius: 50%;
border: 1px dashed rgba(6,182,212,0.25);
animation: aiOrbitSpin 20s linear infinite;
}
.ai-orbit-2 {
position: absolute;
inset: 30px;
border-radius: 50%;
border: 1px dashed rgba(139,92,246,0.25);
animation: aiOrbitSpin 14s linear infinite reverse;
}
.ai-orbit-3 {
position: absolute;
inset: 70px;
border-radius: 50%;
border: 1px dashed rgba(236,72,153,0.2);
animation: aiOrbitSpin 10s linear infinite;
}
@keyframes aiOrbitSpin {
from { transform: rotate(0deg); }
to { transform: rotate(360deg); }
}
.ai-node {
position: absolute;
width: 34px;
height: 34px;
border-radius: 12px;
display: flex;
align-items: center;
justify-content: center;
background: rgba(15,15,15,0.9);
border: 1px solid rgba(6,182,212,0.4);
box-shadow: 0 0 20px rgba(6,182,212,0.3);
backdrop-filter: blur(10px);
}
.ai-node svg { width: 16px; height: 16px; }
.ai-node-1 { top: -17px; left: 50%; transform: translateX(-50%); color: #06b6d4; }
.ai-node-2 { top: 50%; right: -17px; transform: translateY(-50%); color: #8b5cf6; border-color: rgba(139,92,246,0.4); box-shadow: 0 0 20px rgba(139,92,246,0.3); }
.ai-node-3 { bottom: -17px; left: 50%; transform: translateX(-50%); color: #ec4899; border-color: rgba(236,72,153,0.4); box-shadow: 0 0 20px rgba(236,72,153,0.3); }
.ai-node-4 { top: 50%; left: -17px; transform: translateY(-50%); color: #10b981; border-color: rgba(16,185,129,0.4); box-shadow: 0 0 20px rgba(16,185,129,0.3); }

.ai-pulse-ring {
position: absolute;
inset: 40px;
border-radius: 50%;
border: 2px solid rgba(6,182,212,0.4);
animation: aiPulseRing 3s ease-out infinite;
}
.ai-pulse-ring-2 {
position: absolute;
inset: 40px;
border-radius: 50%;
border: 2px solid rgba(139,92,246,0.4);
animation: aiPulseRing 3s ease-out infinite 1.5s;
}
@keyframes aiPulseRing {
0% { transform: scale(0.6); opacity: 0.8; }
100% { transform: scale(1.4); opacity: 0; }
}

/* RAG Demo Chat */
.rag-demo {
background: linear-gradient(135deg, rgba(15,15,15,0.9), rgba(10,10,10,0.95));
border: 1px solid rgba(6,182,212,0.2);
border-radius: 20px;
padding: 20px;
position: relative;
overflow: hidden;
}
.rag-demo::before {
content: '';
position: absolute;
top: 0; left: 0; right: 0;
height: 1px;
background: linear-gradient(90deg, transparent, #06b6d4, #8b5cf6, transparent);
}
.rag-header {
display: flex;
align-items: center;
justify-content: space-between;
padding-bottom: 14px;
border-bottom: 1px solid rgba(255,255,255,0.06);
margin-bottom: 16px;
}
.rag-header-left {
display: flex;
align-items: center;
gap: 10px;
}
.rag-avatar {
width: 32px; height: 32px;
border-radius: 10px;
background: linear-gradient(135deg, #06b6d4, #8b5cf6);
display: flex;
align-items: center;
justify-content: center;
box-shadow: 0 0 20px rgba(6,182,212,0.4);
}
.rag-title {
font-size: 13px;
font-weight: 700;
color: #fff;
}
.rag-subtitle {
font-size: 10px;
color: #64748b;
}
.rag-status {
display: inline-flex;
align-items: center;
gap: 5px;
padding: 4px 10px;
border-radius: 999px;
background: rgba(16,185,129,0.1);
border: 1px solid rgba(16,185,129,0.25);
font-size: 10px;
font-weight: 700;
color: #6ee7b7;
}
.rag-status-dot {
width: 5px; height: 5px;
border-radius: 50%;
background: #10b981;
box-shadow: 0 0 8px #10b981;
animation: pulseDot 1.6s ease-in-out infinite;
}
.rag-messages {
display: flex;
flex-direction: column;
gap: 12px;
min-height: 280px;
}
.rag-msg {
display: flex;
gap: 10px;
align-items: flex-start;
opacity: 0;
transform: translateY(10px);
animation: ragMsgIn 0.5s ease-out forwards;
}
@keyframes ragMsgIn {
to { opacity: 1; transform: translateY(0); }
}
.rag-msg-user { justify-content: flex-end; }
.rag-msg-bubble {
max-width: 80%;
padding: 10px 14px;
border-radius: 14px;
font-size: 12.5px;
line-height: 1.5;
}
.rag-msg-user .rag-msg-bubble {
background: linear-gradient(135deg, rgba(59,130,246,0.2), rgba(139,92,246,0.15));
border: 1px solid rgba(59,130,246,0.25);
color: #e0e7ff;
border-bottom-right-radius: 4px;
}
.rag-msg-ai .rag-msg-bubble {
background: rgba(255,255,255,0.03);
border: 1px solid rgba(6,182,212,0.2);
color: #cbd5e1;
border-bottom-left-radius: 4px;
}
.rag-msg-avatar {
width: 28px; height: 28px;
border-radius: 8px;
flex-shrink: 0;
display: flex;
align-items: center;
justify-content: center;
}
.rag-msg-user .rag-msg-avatar {
background: linear-gradient(135deg, #3b82f6, #8b5cf6);
order: 2;
}
.rag-msg-ai .rag-msg-avatar {
background: linear-gradient(135deg, #06b6d4, #8b5cf6);
box-shadow: 0 0 15px rgba(6,182,212,0.3);
}
.rag-sources {
margin-top: 10px;
padding: 10px;
border-radius: 10px;
background: rgba(6,182,212,0.05);
border: 1px solid rgba(6,182,212,0.15);
}
.rag-sources-title {
font-size: 10px;
font-weight: 700;
color: #06b6d4;
text-transform: uppercase;
letter-spacing: 0.1em;
margin-bottom: 6px;
display: flex;
align-items: center;
gap: 6px;
}
.rag-source-item {
display: flex;
align-items: center;
gap: 8px;
padding: 5px 0;
font-size: 11px;
color: #94a3b8;
}
.rag-source-item + .rag-source-item {
border-top: 1px dashed rgba(255,255,255,0.05);
}
.rag-source-icon {
width: 18px; height: 18px;
border-radius: 4px;
background: rgba(6,182,212,0.15);
display: flex;
align-items: center;
justify-content: center;
flex-shrink: 0;
}
.rag-source-icon svg { width: 10px; height: 10px; color: #06b6d4; }
.rag-source-name {
flex: 1;
color: #cbd5e1;
font-weight: 500;
}
.rag-source-score {
font-size: 10px;
color: #10b981;
font-weight: 700;
}
.rag-typing {
display: inline-flex;
gap: 3px;
padding: 4px 0;
}
.rag-typing span {
width: 5px; height: 5px;
border-radius: 50%;
background: #06b6d4;
animation: ragTyping 1.4s ease-in-out infinite;
}
.rag-typing span:nth-child(2) { animation-delay: 0.2s; }
.rag-typing span:nth-child(3) { animation-delay: 0.4s; }
@keyframes ragTyping {
0%, 60%, 100% { opacity: 0.3; transform: translateY(0); }
30% { opacity: 1; transform: translateY(-3px); }
}
.rag-pipeline {
display: flex;
align-items: center;
justify-content: space-between;
gap: 8px;
margin-top: 16px;
padding: 12px;
border-radius: 12px;
background: rgba(255,255,255,0.02);
border: 1px solid rgba(255,255,255,0.05);
}
.rag-step {
flex: 1;
display: flex;
flex-direction: column;
align-items: center;
gap: 6px;
position: relative;
}
.rag-step-icon {
width: 32px; height: 32px;
border-radius: 10px;
display: flex;
align-items: center;
justify-content: center;
background: rgba(6,182,212,0.1);
border: 1px solid rgba(6,182,212,0.25);
transition: all 0.4s ease;
}
.rag-step-icon svg { width: 14px; height: 14px; color: #06b6d4; }
.rag-step.active .rag-step-icon {
background: linear-gradient(135deg, rgba(6,182,212,0.3), rgba(139,92,246,0.2));
border-color: rgba(6,182,212,0.6);
box-shadow: 0 0 20px rgba(6,182,212,0.4);
transform: scale(1.1);
}
.rag-step-label {
font-size: 9px;
color: #64748b;
font-weight: 600;
text-transform: uppercase;
letter-spacing: 0.05em;
text-align: center;
}
.rag-step.active .rag-step-label { color: #06b6d4; }
.rag-step-arrow {
color: rgba(255,255,255,0.15);
flex-shrink: 0;
}
.rag-step.active + .rag-step-arrow,
.rag-step-arrow.active { color: #06b6d4; }

/* ===== ПОЛНАЯ АДАПТИВНОСТЬ ===== */
/* Большие ноутбуки и маленькие десктопы (до 1200px) */
@media (max-width: 1200px) {
.blob-1 { width: 500px; height: 500px; }
.blob-2 { width: 600px; height: 600px; }
.blob-3 { width: 400px; height: 400px; }
.blob-4 { width: 350px; height: 350px; }
}
/* Маленькие ноутбуки и большие планшеты (до 1024px) */
@media (max-width: 1024px) {
.hero-title { font-size: 3.5rem !important; }
.stat-num { font-size: 2rem; }
.blob-1, .blob-2 { width: 400px; height: 400px; }
.blob-3, .blob-4 { width: 300px; height: 300px; }
.superadmin-card { padding: 32px; }
#donutChart { width: 220px !important; height: 220px !important; }
.ai-brain-wrap { width: 280px; height: 280px; }
}
/* Планшеты (до 768px) */
@media (max-width: 768px) {
.hero-title { font-size: 2.5rem !important; line-height: 1.1 !important; }
.stat-num { font-size: 1.75rem; }
.blob-1, .blob-2, .blob-3, .blob-4 { opacity: 0.1; }
.blob-1, .blob-2 { width: 300px; height: 300px; }
.blob-3, .blob-4 { width: 250px; height: 250px; }
.superadmin-card { padding: 24px; }
.superadmin-card .w-24 { width: 80px; height: 80px; }
.superadmin-card .text-3xl { font-size: 1.5rem; }
#donutChart { width: 200px !important; height: 200px !important; }
.donut-header-bar { padding: 12px 16px; }
.donut-header-bar .text-2xl { font-size: 1.25rem; }
#mainChart { height: 240px !important; }
.glass { padding: 20px; }
section { padding-top: 60px !important; padding-bottom: 60px !important; }
.faceid-tilt { transform: none !important; }
.faceid-layer-frame, .faceid-layer-status, .faceid-layer-chip { transform: none !important; }
.faceid-layer-chip { display: none !important; }
.ai-brain-wrap { width: 240px; height: 240px; }
.ai-brain-core svg { width: 140px; height: 140px; }
.ai-node { width: 28px; height: 28px; }
.ai-node svg { width: 13px; height: 13px; }
.rag-pipeline { flex-wrap: wrap; }
.rag-step-arrow { display: none; }
}
/* Большие телефоны (до 576px) */
@media (max-width: 576px) {
.hero-title { font-size: 2rem !important; }
.stat-num { font-size: 1.5rem; }
.btn-primary, .btn-outline { padding: 10px 16px; font-size: 12px; }
.blob-1, .blob-2, .blob-3, .blob-4 { display: none; }
.superadmin-card { padding: 20px; }
.superadmin-card .w-24 { width: 70px; height: 70px; }
.superadmin-card .text-3xl { font-size: 1.25rem; }
.superadmin-card .text-2xl { font-size: 1.125rem; }
#donutChart { width: 180px !important; height: 180px !important; }
.donut-header-bar { flex-direction: column; align-items: flex-start; gap: 8px; }
#mainChart { height: 200px !important; }
.glass { padding: 16px; border-radius: 20px !important; }
.card-3d { border-radius: 20px !important; }
section { padding-top: 48px !important; padding-bottom: 48px !important; }
.icon-blue, .icon-purple, .icon-green, .icon-orange, .icon-pink, .icon-indigo, .icon-cyan, .icon-ai {
width: 44px !important;
height: 44px !important;
}
.icon-blue svg, .icon-purple svg, .icon-green svg, .icon-orange svg, .icon-pink svg, .icon-indigo svg, .icon-cyan svg, .icon-ai svg {
width: 20px !important;
height: 20px !important;
}
.nav-link { font-size: 13px; }
footer .grid { gap: 24px; }
.ai-brain-wrap { width: 220px; height: 220px; }
}
/* Телефоны (до 480px) */
@media (max-width: 480px) {
.hero-title { font-size: 1.75rem !important; }
.stat-num { font-size: 1.25rem; }
.btn-primary, .btn-outline { padding: 9px 14px; font-size: 11px; gap: 6px; }
.btn-primary svg, .btn-outline svg { width: 14px; height: 14px; }
.superadmin-card { padding: 16px; }
.superadmin-card .w-24 { width: 60px; height: 60px; }
.superadmin-card .text-3xl { font-size: 1.125rem; }
.superadmin-card .text-2xl { font-size: 1rem; }
.superadmin-card p { font-size: 12px; }
#donutChart { width: 160px !important; height: 160px !important; }
.donut-center-text .text-3xl { font-size: 1.5rem; }
.donut-center-text span { font-size: 9px !important; }
#mainChart { height: 180px !important; }
.glass { padding: 14px; border-radius: 16px !important; }
.card-3d { border-radius: 16px !important; }
section { padding-top: 40px !important; padding-bottom: 40px !important; }
.text-3xl, .text-4xl, .text-5xl { font-size: 1.5rem !important; }
.text-sm { font-size: 12px; }
.text-xs { font-size: 10px; }
.text-base { font-size: 13px; }
.mb-14 { margin-bottom: 32px !important; }
.mb-16 { margin-bottom: 40px !important; }
.gap-5 { gap: 12px !important; }
.p-6 { padding: 16px !important; }
.p-5 { padding: 14px !important; }
footer .grid { grid-template-columns: 1fr !important; gap: 20px !important; }
footer .text-xs { font-size: 11px; }
footer .flex-col { gap: 12px; }
.lang-select { padding: 5px 24px 5px 8px; font-size: 10px; }
.rag-demo { padding: 14px; }
.rag-msg-bubble { font-size: 11.5px; padding: 8px 12px; }
}
/* Очень маленькие телефоны (до 380px) */
@media (max-width: 380px) {
.hero-title { font-size: 1.5rem !important; }
.stat-num { font-size: 1.125rem; }
.btn-primary, .btn-outline { padding: 8px 12px; font-size: 10px; }
.superadmin-card { padding: 14px; }
.superadmin-card .w-24 { width: 50px; height: 50px; }
.superadmin-card .text-3xl { font-size: 1rem; }
#donutChart { width: 140px !important; height: 140px !important; }
.donut-center-text .text-3xl { font-size: 1.25rem; }
#mainChart { height: 160px !important; }
.glass { padding: 12px; }
section { padding-top: 32px !important; padding-bottom: 32px !important; }
.text-3xl, .text-4xl, .text-5xl { font-size: 1.25rem !important; }
.grid-cols-2 { grid-template-columns: 1fr !important; }
.ai-brain-wrap { width: 200px; height: 200px; }
}
/* Мобильное меню */
@media (max-width: 1024px) {
#mobileMenu {
max-height: calc(100vh - 64px);
overflow-y: auto;
}
}
/* Отключаем 3D эффект на мобильных для производительности */
@media (max-width: 768px) {
.card-3d {
transform: none !important;
}
.card-3d:hover {
transform: none !important;
}
.card-3d .card-inner {
transform: none !important;
}
.card-3d:hover .card-inner {
transform: none !important;
}
.card-shine {
display: none !important;
}
}
/* Скрываем scroll indicator на мобильных */
@media (max-width: 768px) {
.absolute.bottom-8 {
display: none;
}
}
</style>
</head>
<body class="bg-grid">
<!-- Colorful Background Blobs -->
<div class="bg-blob blob-1"></div>
<div class="bg-blob blob-2"></div>
<div class="bg-blob blob-3"></div>
<div class="bg-blob blob-4"></div>
<!-- 3D Background -->
<canvas id="three-bg"></canvas>
<div class="content-wrap">
<!-- Navigation -->
<nav id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-500">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="flex items-center justify-between h-16 lg:h-18">
{{-- Логотип в навигации --}}
<a href="#" id="logoLink" class="flex items-center gap-3 group">
<img src="{{ asset('img/dss.png') }}"
alt="DocSign"
class="w-16 h-16 rounded-xl object-contain group-hover:scale-105 transition-all duration-300 cursor-pointer">
<span class="text-lg font-bold tracking-tight text-white">Doc<span class="gradient-accent">Sign</span></span>
</a>
{{-- Модальное окно для большого логотипа --}}
<div id="logoModal" class="fixed inset-0 z-[100] hidden">
{{-- Затемнённый фон --}}
<div class="absolute inset-0 bg-black/90 backdrop-blur-sm" id="modalBackdrop"></div>
{{-- Контент модального окна --}}
<div class="relative min-h-screen flex items-center justify-center p-4">
<div class="relative bg-gradient-to-br from-zinc-900 to-black rounded-3xl p-16 shadow-2xl border border-white/10 max-w-3xl w-full text-center transform scale-95 opacity-0 transition-all duration-300" id="modalContent">
{{-- Кнопка закрытия --}}
<button id="closeModal" class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
</svg>
</button>
{{-- Большой логотип --}}
<img src="{{ asset('img/dss.png') }}"
alt="DocSign"
class="w-96 h-96 object-contain mx-auto mb-8 drop-shadow-2xl">
{{-- Название --}}
<h2 class="text-5xl font-bold text-white">Doc<span class="bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 bg-clip-text text-transparent">Sign</span></h2>
</div>
</div>
</div>
{{-- JavaScript для модального окна --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
const logoLink = document.getElementById('logoLink');
const modal = document.getElementById('logoModal');
const modalContent = document.getElementById('modalContent');
const closeModal = document.getElementById('closeModal');
const backdrop = document.getElementById('modalBackdrop');
// Открытие модального окна
logoLink.addEventListener('click', function(e) {
e.preventDefault();
modal.classList.remove('hidden');
setTimeout(() => {
modalContent.classList.remove('scale-95', 'opacity-0');
modalContent.classList.add('scale-100', 'opacity-100');
}, 10);
});
// Закрытие по кнопке
closeModal.addEventListener('click', closeModalFunc);
// Закрытие по клику на фон
backdrop.addEventListener('click', closeModalFunc);
// Закрытие по Escape
document.addEventListener('keydown', function(e) {
if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
closeModalFunc();
}
});
function closeModalFunc() {
modalContent.classList.remove('scale-100', 'opacity-100');
modalContent.classList.add('scale-95', 'opacity-0');
setTimeout(() => {
modal.classList.add('hidden');
}, 300);
}
});
</script>
<div class="hidden lg:flex items-center gap-4 xl:gap-7">
<a href="#features" class="nav-link text-sm font-medium text-gray-400" data-i18n="nav_features">Возможности</a>
<a href="#faceid" class="nav-link text-sm font-medium text-gray-400" data-i18n="nav_faceid">Face ID</a>
<a href="#ai" class="nav-link text-sm font-medium text-gray-400" data-i18n="nav_ai">ИИ</a>
<a href="#departments" class="nav-link text-sm font-medium text-gray-400" data-i18n="nav_departments">Отделы</a>
<a href="#geography" class="nav-link text-sm font-medium text-gray-400" data-i18n="nav_geography">География</a>
<a href="#analytics" class="nav-link text-sm font-medium text-gray-400" data-i18n="nav_analytics">Аналитика</a>
<a href="#security" class="nav-link text-sm font-medium text-gray-400" data-i18n="nav_security">Безопасность</a>
<a href="#admin" class="nav-link text-sm font-medium text-gray-400" data-i18n="nav_admin">Админ</a>
<a href="#contact" class="nav-link text-sm font-medium text-gray-400" data-i18n="nav_contact">Контакты</a>
</div>
<div class="flex items-center gap-3">
<div class="hidden sm:block lang-select-wrap">
<select class="lang-select" id="lang-select">
<option value="ru">RU</option>
<option value="en">EN</option>
<option value="tj">TJ</option>
</select>
<svg class="lang-select-arrow w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
</svg>
</div>
<a href="/login" class="btn-primary px-4 py-2 rounded-lg text-sm font-semibold shadow-lg">
<span data-i18n="nav_start">Начать</span>
</a>
<button id="mobileMenuBtn" class="lg:hidden p-2 text-gray-300 hover:text-white transition-colors">
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
</svg>
</button>
</div>
</div>
</div>
<div id="mobileMenu" class="hidden lg:hidden glass-strong border-t border-white/5">
<div class="px-4 py-4 space-y-3">
<div class="pb-3 border-b border-white/5">
<div class="lang-select-wrap">
<select class="lang-select" id="lang-select-mobile">
<option value="ru">RU — Русский</option>
<option value="en">EN — English</option>
<option value="tj">TJ — Тоҷикӣ</option>
</select>
<svg class="lang-select-arrow w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
</svg>
</div>
</div>
<a href="#features" class="block text-sm font-medium text-gray-300 hover:text-white py-2" data-i18n="nav_features">Возможности</a>
<a href="#faceid" class="block text-sm font-medium text-gray-300 hover:text-white py-2" data-i18n="nav_faceid">Face ID</a>
<a href="#ai" class="block text-sm font-medium text-gray-300 hover:text-white py-2" data-i18n="nav_ai">ИИ</a>
<a href="#departments" class="block text-sm font-medium text-gray-300 hover:text-white py-2" data-i18n="nav_departments">Отделы</a>
<a href="#geography" class="block text-sm font-medium text-gray-300 hover:text-white py-2" data-i18n="nav_geography">География</a>
<a href="#analytics" class="block text-sm font-medium text-gray-300 hover:text-white py-2" data-i18n="nav_analytics">Аналитика</a>
<a href="#security" class="block text-sm font-medium text-gray-300 hover:text-white py-2" data-i18n="nav_security">Безопасность</a>
<a href="#admin" class="block text-sm font-medium text-gray-300 hover:text-white py-2" data-i18n="nav_admin">Админ</a>
<a href="#contact" class="block text-sm font-medium text-gray-300 hover:text-white py-2" data-i18n="nav_contact">Контакты</a>
</div>
</div>
</nav>
<!-- Hero Section -->
<section id="hero" class="relative min-h-screen flex items-center justify-center overflow-hidden pt-20">
<div class="absolute inset-0" style="background: radial-gradient(ellipse at 50% 30%, rgba(59,130,246,0.08) 0%, transparent 60%);"></div>
<div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
<div class="inline-flex items-center gap-2 glass rounded-full px-4 py-1.5 mb-8">
<span class="pulse-dot"></span>
<span class="text-xs font-medium text-gray-300" data-i18n="hero_badge">Система нового поколения</span>
</div>
<h1 class="hero-title text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-black tracking-tight mb-6 leading-[1.05]">
<span class="block gradient-text text-3xl sm:text-4xl md:text-5xl lg:text-6xl" data-i18n="hero_title_1">Электронный документооборот</span>
<span class="hero-arrows flex items-end justify-center gap-10 sm:gap-16 md:gap-24 text-4xl sm:text-5xl md:text-6xl mt-3 mb-3" aria-hidden="true">
</h1>
<p class="max-w-2xl mx-auto text-base sm:text-lg text-gray-400 mb-10 leading-relaxed" data-i18n="hero_subtitle">
DocSign — современная платформа для работы с документами. Подписывайте, отправляйте и храните файлы в один клик. Просто и надёжно — для людей, чтобы работа стала легче.
</p>
<div class="flex flex-wrap items-center justify-center gap-3 mb-16">
<a href="/login" class="btn-primary px-6 py-3 rounded-xl text-sm font-semibold shadow-2xl flex items-center gap-2">
<span data-i18n="hero_cta">Начать работу</span>
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
</a>
<a href="#features" class="btn-outline px-6 py-3 rounded-xl text-sm font-semibold text-gray-300 flex items-center gap-2">
<span data-i18n="hero_learn">Узнать больше</span>
</a>
</div>
<!-- Stats -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 max-w-4xl mx-auto">
<div class="stat-card-blue rounded-2xl p-4 text-left card-3d card-blue">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="text-[10px] uppercase tracking-[0.2em] text-blue-400 font-bold mb-2" data-i18n="stat_speed">Скорость</div>
<div class="stat-num gradient-text"><span class="counter" data-target="60">0</span><span class="text-base font-bold text-gray-400 ml-1" data-i18n="stat_sec">сек</span></div>
<div class="text-xs text-gray-500 mt-1" data-i18n="stat_speed_desc">на один документ</div>
</div>
</div>
<div class="stat-card-purple rounded-2xl p-4 text-left card-3d card-purple">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="text-[10px] uppercase tracking-[0.2em] text-purple-400 font-bold mb-2" data-i18n="stat_users">Пользователи</div>
<div class="stat-num gradient-text">
<span class="counter" data-target="{{ \App\Models\User::count() }}">0</span>
</div>
<div class="text-xs text-gray-500 mt-1" data-i18n="stat_users_desc">зарегистрировано</div>
</div>
</div>
<div class="stat-card-green rounded-2xl p-4 text-left card-3d card-green">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="text-[10px] uppercase tracking-[0.2em] text-green-400 font-bold mb-2" data-i18n="stat_access">Доступ</div>
<div class="stat-num gradient-text">24/7</div>
<div class="text-xs text-gray-500 mt-1" data-i18n="stat_access_desc">всегда онлайн</div>
</div>
</div>
<div class="stat-card-orange rounded-2xl p-4 text-left card-3d card-orange">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="text-[10px] uppercase tracking-[0.2em] text-orange-400 font-bold mb-2" data-i18n="stat_protection">Защита</div>
<div class="stat-num gradient-text"><span class="counter" data-target="100">0</span>%</div>
<div class="text-xs text-gray-500 mt-1" data-i18n="stat_protection_desc">юридическая сила</div>
</div>
</div>
</div>
</div>
<div class="absolute bottom-8 left-1/2 -translate-x-1/2">
<div class="w-6 h-10 rounded-full border border-gray-600/50 flex justify-center pt-2">
<div class="w-1 h-3 bg-blue-400 rounded-full animate-pulse"></div>
</div>
</div>
</section>
<!-- ===================== FACE ID SECTION (NEW) ===================== -->
<section id="faceid" class="relative py-24 overflow-hidden">
<div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">
<!-- Left: 3D Face Scanner -->
<div class="scroll-reveal order-2 lg:order-1">
<div class="faceid-perspective max-w-md mx-auto">
<div id="faceTiltWrap">
<div id="faceScanner" class="faceid-tilt glass rounded-3xl p-6 md:p-8 relative">
<!-- Corner brackets + face -->
<div class="faceid-layer-frame relative mx-auto max-w-[280px]">
<div class="faceid-frame-corner corner-tl"></div>
<div class="faceid-frame-corner corner-tr"></div>
<div class="faceid-frame-corner corner-bl"></div>
<div class="faceid-frame-corner corner-br"></div>
<!-- Verified check badge -->
<div class="face-check">
<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
</svg>
</div>
<!-- Dot-mesh face (generated by JS) -->
<svg id="faceDots" viewBox="0 0 220 224" class="w-full h-auto relative z-[3]"></svg>
<!-- Scan line -->
<div class="scan-line"></div>
</div>
<!-- Status pill -->
<div class="faceid-layer-status flex justify-center mt-6">
<div id="faceStatusPill" class="face-status">
<span id="faceStatusDot" class="face-status-dot"></span>
<span id="faceStatusText" data-i18n="faceid_status_scanning">Сканирование лица...</span>
</div>
</div>
<!-- Floating chips -->
<div class="faceid-layer-chip absolute -top-4 -left-3 glass rounded-2xl px-4 py-2.5 flex items-center gap-2 z-20 shadow-xl shadow-black/40">
<span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
<span class="text-xs font-bold text-white">99.9% <span class="text-gray-400 font-medium" data-i18n="faceid_accuracy">точность</span></span>
</div>
<div class="faceid-layer-chip absolute -bottom-4 -right-3 glass rounded-2xl px-4 py-2.5 flex items-center gap-2 z-20 shadow-xl shadow-black/40">
<span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
<span class="text-xs font-bold text-white">0.3 <span class="text-gray-400 font-medium" data-i18n="faceid_speed">сек</span></span>
</div>
</div>
</div>
<div class="text-center mt-8">
<span class="inline-flex items-center gap-2 text-[11px] uppercase tracking-[0.2em] text-gray-500 font-bold">
<span class="pulse-dot" style="width:6px;height:6px;"></span>
<span data-i18n="faceid_demo_note">Живая демонстрация</span>
</span>
</div>
</div>
</div>
<!-- Right: Report text -->
<div class="scroll-reveal order-1 lg:order-2" style="transition-delay:0.15s">
<span class="inline-flex items-center gap-2 text-xs font-semibold text-cyan-400 uppercase tracking-widest mb-4">
<span class="w-8 h-px bg-cyan-400"></span>
<span data-i18n="faceid_label">Face ID • Биометрия</span>
</span>
<div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-500/10 border border-blue-500/25 mb-5">
<svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h5m0 0l-3-3m3 3l-3 3M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
</svg>
<span class="text-[11px] font-bold uppercase tracking-wider text-blue-300" data-i18n="faceid_report_badge">Краткий отчёт</span>
</div>
<h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white mb-5 tracking-tight leading-tight" data-i18n="faceid_title">Вход и подпись — по вашему лицу</h2>
<p class="text-sm sm:text-base text-gray-400 leading-relaxed mb-8" data-i18n="faceid_text">
Если у вас есть компания — Face ID станет вашим помощником. Сотрудник смотрит в камеру, система за долю секунды подтверждает личность и подписывает документ. Руководитель получает краткий отчёт: кто, когда и что подписал.
</p>
<!-- Bullet points -->
<div class="space-y-4 mb-8">
<div class="flex items-start gap-4 group">
<div class="icon-blue w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform duration-300">
<svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
</svg>
</div>
<div>
<div class="text-sm font-bold text-white mb-0.5" data-i18n="faceid_b1_t">Мгновенное подтверждение</div>
<div class="text-xs text-gray-400 leading-relaxed" data-i18n="faceid_b1_d">Распознавание лица занимает 0.3 секунды — быстрее любого пароля.</div>
</div>
</div>
<div class="flex items-start gap-4 group">
<div class="icon-purple w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform duration-300">
<svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h5m0 0l-3-3m3 3l-3 3M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
</svg>
</div>
<div>
<div class="text-sm font-bold text-white mb-0.5" data-i18n="faceid_b2_t">Полные отчёты</div>
<div class="text-xs text-gray-400 leading-relaxed" data-i18n="faceid_b2_d">Каждая проверка сохраняется: кто вошёл, когда и что подписал.</div>
</div>
</div>
<div class="flex items-start gap-4 group">
<div class="icon-green w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform duration-300">
<svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
</svg>
</div>
<div>
<div class="text-sm font-bold text-white mb-0.5" data-i18n="faceid_b3_t">Для всей компании</div>
<div class="text-xs text-gray-400 leading-relaxed" data-i18n="faceid_b3_d">Подключите сотрудников, назначьте роли — и работайте без бумаг.</div>
</div>
</div>
</div>
<div class="flex flex-wrap gap-3">
<a href="/login" class="btn-primary px-6 py-3 rounded-xl text-sm font-semibold shadow-2xl flex items-center gap-2">
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
</svg>
<span data-i18n="faceid_cta">Подключить Face ID</span>
</a>
<a href="#analytics" class="btn-outline px-6 py-3 rounded-xl text-sm font-semibold text-gray-300 flex items-center gap-2">
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
</svg>
<span data-i18n="faceid_report">Смотреть отчёты</span>
</a>
</div>
</div>
</div>
</div>
</section>

<!-- ===================== AI / RAG SECTION (NEW) ===================== -->
<section id="ai" class="relative py-24 overflow-hidden ai-section-bg">
<div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<!-- Section header -->
<div class="text-center mb-14 scroll-reveal">
<span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-widest mb-4 px-4 py-1.5 rounded-full bg-cyan-500/10 border border-cyan-500/25" style="color:#22d3ee;">
<span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
<span data-i18n="ai_label">Искусственный интеллект • RAG</span>
</span>
<h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white mb-4 tracking-tight">
<span class="gradient-ai" data-i18n="ai_title">Умный помощник в каждом документе</span>
</h2>
<p class="max-w-2xl mx-auto text-sm sm:text-base text-gray-400" data-i18n="ai_subtitle">
ИИ на базе технологии RAG понимает ваши документы, находит нужную информацию за секунды и отвечает на вопросы — как живой эксперт.
</p>
</div>

<!-- Main grid: Brain + RAG Demo -->
<div class="grid lg:grid-cols-2 gap-8 mb-10 items-center">
<!-- Left: AI Brain visualization -->
<div class="scroll-reveal">
<div class="glass rounded-3xl p-6 md:p-8 card-3d card-ai relative">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="flex items-center justify-between mb-5">
<div class="flex items-center gap-2">
<div class="icon-ai w-10 h-10 rounded-xl flex items-center justify-center">
<svg class="w-5 h-5" fill="none" stroke="#22d3ee" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
</svg>
</div>
<div>
<div class="text-sm font-bold text-white" data-i18n="ai_brain_title">Нейросеть DocSign</div>
<div class="text-[10px] text-gray-500 uppercase tracking-wider" data-i18n="ai_brain_sub">RAG • Embeddings • LLM</div>
</div>
</div>
<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/25 text-[10px] font-bold text-emerald-400">
<span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
<span data-i18n="ai_brain_online">Онлайн</span>
</span>
</div>

<!-- Brain visualization -->
<div class="ai-brain-wrap">
<!-- Pulse rings -->
<div class="ai-pulse-ring"></div>
<div class="ai-pulse-ring-2"></div>
<!-- Orbits -->
<div class="ai-orbit"></div>
<div class="ai-orbit-2"></div>
<div class="ai-orbit-3"></div>
<!-- Nodes on orbit -->
<div class="ai-node ai-node-1" title="Поиск">
<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
</div>
<div class="ai-node ai-node-2" title="Документы">
<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
</div>
<div class="ai-node ai-node-3" title="Ответ">
<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
</div>
<div class="ai-node ai-node-4" title="Анализ">
<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
</div>
<!-- Core brain -->
<div class="ai-brain-core">
<svg viewBox="0 0 200 200">
<defs>
<radialGradient id="brainGrad" cx="50%" cy="50%">
<stop offset="0%" stop-color="#22d3ee" stop-opacity="0.9"/>
<stop offset="50%" stop-color="#8b5cf6" stop-opacity="0.6"/>
<stop offset="100%" stop-color="#ec4899" stop-opacity="0.3"/>
</radialGradient>
<filter id="brainGlow">
<feGaussianBlur stdDeviation="4" result="coloredBlur"/>
<feMerge>
<feMergeNode in="coloredBlur"/>
<feMergeNode in="SourceGraphic"/>
</feMerge>
</filter>
</defs>
<!-- Outer glow -->
<circle cx="100" cy="100" r="70" fill="url(#brainGrad)" opacity="0.3" filter="url(#brainGlow)"/>
<!-- Brain shape -->
<g filter="url(#brainGlow)" stroke="#22d3ee" stroke-width="1.5" fill="none" opacity="0.9">
<path d="M 70 70 Q 60 50, 80 45 Q 95 35, 110 45 Q 130 40, 135 60 Q 150 65, 145 85 Q 155 100, 140 115 Q 145 135, 125 140 Q 115 155, 95 150 Q 75 155, 65 140 Q 50 130, 55 110 Q 45 95, 55 80 Q 50 65, 70 70 Z"/>
<!-- Inner connections -->
<path d="M 80 75 L 120 75 L 125 100 L 115 125 L 85 125 L 75 100 Z" opacity="0.6"/>
<path d="M 100 60 L 100 140" opacity="0.4"/>
<path d="M 65 100 L 135 100" opacity="0.4"/>
<!-- Nodes -->
<circle cx="100" cy="100" r="8" fill="#22d3ee" opacity="0.9"/>
<circle cx="80" cy="75" r="4" fill="#8b5cf6"/>
<circle cx="120" cy="75" r="4" fill="#8b5cf6"/>
<circle cx="125" cy="100" r="4" fill="#ec4899"/>
<circle cx="115" cy="125" r="4" fill="#ec4899"/>
<circle cx="85" cy="125" r="4" fill="#10b981"/>
<circle cx="75" cy="100" r="4" fill="#10b981"/>
</g>
</svg>
</div>
</div>

<!-- Stats under brain -->
<div class="grid grid-cols-3 gap-3 mt-6">
<div class="text-center p-3 rounded-xl bg-white/[0.02] border border-white/5">
<div class="text-lg font-black gradient-ai">0.8<span class="text-xs text-gray-500">с</span></div>
<div class="text-[9px] uppercase tracking-wider text-gray-500 font-bold mt-1" data-i18n="ai_stat_speed">Ответ</div>
</div>
<div class="text-center p-3 rounded-xl bg-white/[0.02] border border-white/5">
<div class="text-lg font-black gradient-ai">98%</div>
<div class="text-[9px] uppercase tracking-wider text-gray-500 font-bold mt-1" data-i18n="ai_stat_accuracy">Точность</div>
</div>
<div class="text-center p-3 rounded-xl bg-white/[0.02] border border-white/5">
<div class="text-lg font-black gradient-ai">24/7</div>
<div class="text-[9px] uppercase tracking-wider text-gray-500 font-bold mt-1" data-i18n="ai_stat_uptime">Работа</div>
</div>
</div>
</div>
</div>
</div>

<!-- Right: RAG Demo Chat -->
<div class="scroll-reveal" style="transition-delay:0.15s">
<div class="rag-demo">
<div class="rag-header">
<div class="rag-header-left">
<div class="rag-avatar">
<svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
</svg>
</div>
<div>
<div class="rag-title">DocSign AI</div>
<div class="rag-subtitle" data-i18n="ai_rag_sub">RAG • Поиск по базе знаний</div>
</div>
</div>
<div class="rag-status">
<span class="rag-status-dot"></span>
<span data-i18n="ai_rag_active">Активен</span>
</div>
</div>

<!-- Messages container -->
<div class="rag-messages" id="ragMessages">
<!-- Messages will be injected by JS -->
</div>

<!-- Pipeline visualization -->
<div class="rag-pipeline">
<div class="rag-step" data-step="1">
<div class="rag-step-icon">
<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
</div>
<div class="rag-step-label" data-i18n="ai_step_query">Запрос</div>
</div>
<svg class="rag-step-arrow w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
<div class="rag-step" data-step="2">
<div class="rag-step-icon">
<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
</div>
<div class="rag-step-label" data-i18n="ai_step_retrieve">Поиск</div>
</div>
<svg class="rag-step-arrow w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
<div class="rag-step" data-step="3">
<div class="rag-step-icon">
<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
</div>
<div class="rag-step-label" data-i18n="ai_step_generate">ИИ</div>
</div>
<svg class="rag-step-arrow w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
<div class="rag-step" data-step="4">
<div class="rag-step-icon">
<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
</div>
<div class="rag-step-label" data-i18n="ai_step_answer">Ответ</div>
</div>
</div>
</div>
</div>
</div>

<!-- AI Features grid -->
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
<!-- Feature 1 -->
<div class="glass rounded-3xl p-6 card-3d card-ai scroll-reveal group">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="icon-ai w-12 h-12 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
<svg class="w-6 h-6" fill="none" stroke="#22d3ee" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
</svg>
</div>
<h3 class="text-base font-bold text-white mb-2" data-i18n="ai_f1_title">Умный поиск</h3>
<p class="text-sm text-gray-400 leading-relaxed" data-i18n="ai_f1_desc">ИИ понимает смысл вопроса, а не просто слова. Находит нужный документ даже если вы не помните точное название.</p>
</div>
</div>
<!-- Feature 2 -->
<div class="glass rounded-3xl p-6 card-3d card-cyan scroll-reveal group" style="transition-delay:0.1s">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="icon-cyan w-12 h-12 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
<svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
</svg>
</div>
<h3 class="text-base font-bold text-white mb-2" data-i18n="ai_f2_title">База знаний</h3>
<p class="text-sm text-gray-400 leading-relaxed" data-i18n="ai_f2_desc">Все ваши документы превращаются в единую базу знаний. ИИ отвечает на вопросы, ссылаясь на конкретные файлы.</p>
</div>
</div>
<!-- Feature 3 -->
<div class="glass rounded-3xl p-6 card-3d card-purple scroll-reveal group" style="transition-delay:0.2s">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="icon-purple w-12 h-12 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
<svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
</svg>
</div>
<h3 class="text-base font-bold text-white mb-2" data-i18n="ai_f3_title">Генерация ответов</h3>
<p class="text-sm text-gray-400 leading-relaxed" data-i18n="ai_f3_desc">ИИ читает документы и формулирует чёткие ответы. Вы получаете суть без необходимости читать сотни страниц.</p>
</div>
</div>
<!-- Feature 4 -->
<div class="glass rounded-3xl p-6 card-3d card-green scroll-reveal group">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="icon-green w-12 h-12 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
<svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
</svg>
</div>
<h3 class="text-base font-bold text-white mb-2" data-i18n="ai_f4_title">Только ваши данные</h3>
<p class="text-sm text-gray-400 leading-relaxed" data-i18n="ai_f4_desc">ИИ работает только с документами вашей компании. Никаких утечек — все данные остаются внутри системы.</p>
</div>
</div>
<!-- Feature 5 -->
<div class="glass rounded-3xl p-6 card-3d card-orange scroll-reveal group" style="transition-delay:0.1s">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="icon-orange w-12 h-12 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
<svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
</svg>
</div>
<h3 class="text-base font-bold text-white mb-2" data-i18n="ai_f5_title">Анализ документов</h3>
<p class="text-sm text-gray-400 leading-relaxed" data-i18n="ai_f5_desc">ИИ автоматически извлекает ключевые данные: суммы, даты, стороны договора. Экономит часы ручной работы.</p>
</div>
</div>
<!-- Feature 6 -->
<div class="glass rounded-3xl p-6 card-3d card-pink scroll-reveal group" style="transition-delay:0.2s">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="icon-pink w-12 h-12 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
<svg class="w-6 h-6 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
</svg>
</div>
<h3 class="text-base font-bold text-white mb-2" data-i18n="ai_f6_title">Диалог на вашем языке</h3>
<p class="text-sm text-gray-400 leading-relaxed" data-i18n="ai_f6_desc">Общайтесь с ИИ на русском, таджикском или английском. Он понимает контекст и отвечает естественно.</p>
</div>
</div>
</div>

<!-- Bottom CTA card -->
<div class="glass rounded-3xl p-6 md:p-8 card-3d card-ai scroll-reveal relative overflow-hidden">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="absolute -top-20 -right-20 w-60 h-60 rounded-full" style="background: radial-gradient(circle, rgba(6,182,212,0.15) 0%, transparent 70%); pointer-events:none;"></div>
<div class="absolute -bottom-20 -left-20 w-60 h-60 rounded-full" style="background: radial-gradient(circle, rgba(139,92,246,0.15) 0%, transparent 70%); pointer-events:none;"></div>
<div class="relative flex flex-col md:flex-row items-center justify-between gap-6">
<div class="flex items-center gap-5">
<div class="icon-ai w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0">
<svg class="w-7 h-7" fill="none" stroke="#22d3ee" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"/>
</svg>
</div>
<div>
<h3 class="text-xl md:text-2xl font-black text-white mb-1" data-i18n="ai_cta_title">Попробуйте ИИ прямо сейчас</h3>
<p class="text-sm text-gray-400" data-i18n="ai_cta_desc">Задайте вопрос по вашим документам — получите ответ за секунды со ссылками на источники.</p>
</div>
</div>
<div class="flex flex-wrap gap-3">
<a href="/login" class="btn-primary px-6 py-3 rounded-xl text-sm font-semibold shadow-2xl flex items-center gap-2 whitespace-nowrap">
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
</svg>
<span data-i18n="ai_cta_btn">Запустить ИИ</span>
</a>
<a href="#features" class="btn-outline px-6 py-3 rounded-xl text-sm font-semibold text-gray-300 flex items-center gap-2 whitespace-nowrap">
<span data-i18n="ai_cta_learn">Узнать больше</span>
</a>
</div>
</div>
</div>
</div>
</div>
</section>

<!-- Analytics Section -->
<section id="analytics" class="relative py-24 overflow-hidden">
<div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="text-center mb-14 scroll-reveal">
<span class="inline-flex items-center gap-2 text-xs font-semibold text-blue-400 uppercase tracking-widest mb-4">
<span class="w-8 h-px bg-blue-400"></span>
<span data-i18n="analytics_label">Аналитика</span>
<span class="w-8 h-px bg-blue-400"></span>
</span>
<h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white mb-4 tracking-tight" data-i18n="analytics_title">Всё под контролем</h2>
<p class="max-w-2xl mx-auto text-sm sm:text-base text-gray-400" data-i18n="analytics_subtitle">Следите за регистрациями, документами и активностью в реальном времени</p>
</div>
<!-- 3 Analysis Cards -->
<div class="grid md:grid-cols-3 gap-5 mb-6">
<!-- Card 1: Registrations -->
<div class="glass rounded-3xl p-6 card-3d card-blue scroll-reveal">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="flex items-start justify-between mb-5">
<div class="icon-blue w-11 h-11 rounded-xl flex items-center justify-center">
<svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
</svg>
</div>
<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-[10px] font-bold text-emerald-400">
<span class="pulse-dot" style="background:#10b981;box-shadow:0 0 8px #10b981;width:5px;height:5px;"></span>
Live
</span>
</div>
<div class="text-[11px] uppercase tracking-wider text-gray-500 font-bold mb-1.5" data-i18n="analysis_reg_title">Регистрации</div>
<div class="stat-num gradient-text">
<span class="counter" data-target="{{ \App\Models\User::count() }}">0</span>
</div>
<div class="text-xs text-gray-500 mb-5" data-i18n="analysis_reg_desc">пользователей зарегистрировано</div>
<!-- Mini chart -->
<div class="flex items-end gap-1 h-12">
<div class="flex-1 bg-blue-500/20 rounded-sm mini-chart-bar" style="height: 30%"></div>
<div class="flex-1 bg-blue-500/30 rounded-sm mini-chart-bar" style="height: 50%"></div>
<div class="flex-1 bg-blue-500/25 rounded-sm mini-chart-bar" style="height: 40%"></div>
<div class="flex-1 bg-blue-500/40 rounded-sm mini-chart-bar" style="height: 65%"></div>
<div class="flex-1 bg-blue-500/35 rounded-sm mini-chart-bar" style="height: 55%"></div>
<div class="flex-1 bg-blue-500/50 rounded-sm mini-chart-bar" style="height: 80%"></div>
<div class="flex-1 bg-blue-500/70 rounded-sm mini-chart-bar" style="height: 100%"></div>
</div>
</div>
</div>
<!-- Card 2: Documents -->
<div class="glass rounded-3xl p-6 card-3d card-purple scroll-reveal" style="transition-delay:0.1s">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="flex items-start justify-between mb-5">
<div class="icon-purple w-11 h-11 rounded-xl flex items-center justify-center">
<svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
</svg>
</div>
<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-purple-500/10 border border-purple-500/20 text-[10px] font-bold text-purple-400">
<span class="pulse-dot" style="background:#8b5cf6;box-shadow:0 0 8px #8b5cf6;width:5px;height:5px;"></span>
Live
</span>
</div>
<div class="text-[11px] uppercase tracking-wider text-gray-500 font-bold mb-1.5" data-i18n="analysis_doc_title">Документы</div>
<div class="stat-num gradient-text">
<span class="counter" data-target="{{ \App\Models\Document::count() }}">0</span>
</div>
<div class="text-xs text-gray-500 mb-5" data-i18n="analysis_doc_desc">документов отправлено</div>
<!-- Mini line chart -->
<svg viewBox="0 0 200 50" class="w-full h-12" preserveAspectRatio="none">
<defs>
<linearGradient id="docGrad" x1="0" y1="0" x2="0" y2="1">
<stop offset="0%" stop-color="#8b5cf6" stop-opacity="0.4"/>
<stop offset="100%" stop-color="#8b5cf6" stop-opacity="0"/>
</linearGradient>
</defs>
<path d="M 0 35 L 25 28 L 50 32 L 75 20 L 100 25 L 125 15 L 150 18 L 175 10 L 200 5 L 200 50 L 0 50 Z" fill="url(#docGrad)"/>
<path d="M 0 35 L 25 28 L 50 32 L 75 20 L 100 25 L 125 15 L 150 18 L 175 10 L 200 5" stroke="#8b5cf6" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
</div>
</div>
<!-- Card 3: Activity -->
<div class="glass rounded-3xl p-6 card-3d card-green scroll-reveal" style="transition-delay:0.2s">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="flex items-start justify-between mb-5">
<div class="icon-green w-11 h-11 rounded-xl flex items-center justify-center">
<svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
</svg>
</div>
<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-green-500/10 border border-green-500/20 text-[10px] font-bold text-green-400">
<span class="pulse-dot" style="background:#10b981;box-shadow:0 0 8px #10b981;width:5px;height:5px;"></span>
Live
</span>
</div>
<div class="text-[11px] uppercase tracking-wider text-gray-500 font-bold mb-1.5" data-i18n="analysis_activity_title">Активность</div>
<div class="stat-num text-white mb-1"><span class="counter" data-target="100">0</span>%</div>
<div class="text-xs text-gray-500 mb-5" data-i18n="analysis_activity_desc">документов обработано успешно</div>
<!-- Donut chart -->
<div class="relative flex items-center justify-center h-12">
<svg viewBox="0 0 36 36" class="w-16 h-16 -rotate-90">
<circle cx="18" cy="18" r="14" fill="none" stroke="rgba(16,185,129,0.15)" stroke-width="3"/>
<circle cx="18" cy="18" r="14" fill="none" stroke="#10b981" stroke-width="3" stroke-dasharray="88 100" stroke-linecap="round"/>
</svg>
<div class="absolute text-[10px] font-bold text-green-400">100%</div>
</div>
</div>
</div>
</div>
<!-- Big Donut Chart Card -->
<div class="glass rounded-3xl p-6 md:p-8 card-3d scroll-reveal mb-6">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<!-- Header bar -->
<div class="donut-header-bar rounded-2xl px-5 py-3 mb-8 flex items-center justify-between flex-wrap gap-3">
<div class="flex items-center gap-3">
<span class="text-[11px] uppercase tracking-[0.2em] font-bold text-gray-400" data-i18n="donut_period">В МЕСЯЦ</span>
<span class="text-[11px] text-gray-600">:</span>
<span class="text-2xl font-black text-white counter" data-target="54">0</span>
</div>
<div class="flex items-center gap-2 text-xs text-gray-500">
<span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
<span data-i18n="donut_live">Обновлено только что</span>
</div>
</div>
<!-- Donut Chart -->
<div class="flex flex-col lg:flex-row items-center justify-center gap-8 lg:gap-16">
<div class="relative">
<svg id="donutChart" viewBox="0 0 200 200" class="w-64 h-64 md:w-72 md:h-72 -rotate-90">
<circle cx="100" cy="100" r="80" fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="22"/>
<circle class="donut-segment" id="seg1" cx="100" cy="100" r="80" fill="none" stroke="#3b82f6" stroke-width="22" stroke-linecap="round" data-percent="35"/>
<circle class="donut-segment" id="seg2" cx="100" cy="100" r="80" fill="none" stroke="#8b5cf6" stroke-width="22" stroke-linecap="round" data-percent="28"/>
<circle class="donut-segment" id="seg3" cx="100" cy="100" r="80" fill="none" stroke="#10b981" stroke-width="22" stroke-linecap="round" data-percent="22"/>
<circle class="donut-segment" id="seg4" cx="100" cy="100" r="80" fill="none" stroke="#f59e0b" stroke-width="22" stroke-linecap="round" data-percent="15"/>
</svg>
<div class="absolute inset-0 flex flex-col items-center justify-center donut-center-text">
<span class="text-[11px] uppercase tracking-[0.2em] font-bold text-gray-500" data-i18n="donut_center_label">Документы</span>
<span class="text-3xl font-black text-white mt-1">54</span>
<span class="text-[10px] text-gray-500 mt-0.5" data-i18n="donut_center_sub">всего</span>
</div>
</div>
<!-- Legend -->
<div class="flex flex-col gap-3 w-full max-w-xs">
<div class="legend-item flex items-center gap-3 p-3 rounded-xl bg-white/[0.02] border border-white/5">
<span class="w-3.5 h-3.5 rounded-full bg-blue-500 shadow-lg shadow-blue-500/30"></span>
<span class="flex-1 text-sm text-gray-300 font-medium" data-i18n="donut_leg1">Входящие</span>
<span class="text-sm font-bold text-white">19</span>
<span class="text-xs text-gray-500">35%</span>
</div>
<div class="legend-item flex items-center gap-3 p-3 rounded-xl bg-white/[0.02] border border-white/5">
<span class="w-3.5 h-3.5 rounded-full bg-purple-500 shadow-lg shadow-purple-500/30"></span>
<span class="flex-1 text-sm text-gray-300 font-medium" data-i18n="donut_leg2">Отправленные</span>
<span class="text-sm font-bold text-white">15</span>
<span class="text-xs text-gray-500">28%</span>
</div>
<div class="legend-item flex items-center gap-3 p-3 rounded-xl bg-white/[0.02] border border-white/5">
<span class="w-3.5 h-3.5 rounded-full bg-emerald-500 shadow-lg shadow-emerald-500/30"></span>
<span class="flex-1 text-sm text-gray-300 font-medium" data-i18n="donut_leg3">Подписанные</span>
<span class="text-sm font-bold text-white">12</span>
<span class="text-xs text-gray-500">22%</span>
</div>
<div class="legend-item flex items-center gap-3 p-3 rounded-xl bg-white/[0.02] border border-white/5">
<span class="w-3.5 h-3.5 rounded-full bg-amber-500 shadow-lg shadow-amber-500/30"></span>
<span class="flex-1 text-sm text-gray-300 font-medium" data-i18n="donut_leg4">В очереди</span>
<span class="text-sm font-bold text-white">8</span>
<span class="text-xs text-gray-500">15%</span>
</div>
</div>
</div>
</div>
</div>
<!-- Big chart card -->
<div class="glass rounded-3xl p-6 card-3d scroll-reveal">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
<div>
<div class="text-[11px] uppercase tracking-wider text-gray-500 font-bold mb-1" data-i18n="chart_label">Обзор за месяц</div>
<div class="text-base font-bold text-white" data-i18n="chart_title">Динамика работы системы</div>
</div>
<div class="flex items-center gap-4 text-xs">
<div class="flex items-center gap-2">
<span class="w-2 h-2 rounded-full bg-blue-500"></span>
<span class="text-gray-400" data-i18n="chart_reg">Регистрации</span>
</div>
<div class="flex items-center gap-2">
<span class="w-2 h-2 rounded-full bg-purple-500"></span>
<span class="text-gray-400" data-i18n="chart_doc">Документы</span>
</div>
</div>
</div>
<div id="mainChart" class="w-full h-64 sm:h-80"></div>
</div>
</div>
</div>
</section>
<!-- Features Section -->
<section id="features" class="relative py-24 overflow-hidden">
<div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="text-center mb-14 scroll-reveal">
<span class="inline-flex items-center gap-2 text-xs font-semibold text-purple-400 uppercase tracking-widest mb-4">
<span class="w-8 h-px bg-purple-400"></span>
<span data-i18n="features_label">Возможности</span>
<span class="w-8 h-px bg-purple-400"></span>
</span>
<h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white mb-4 tracking-tight" data-i18n="features_title">Всё, что вам нужно</h2>
<p class="max-w-2xl mx-auto text-sm sm:text-base text-gray-400" data-i18n="features_subtitle">Простые инструменты для сложной работы с документами</p>
</div>
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
<div class="glass rounded-3xl p-6 card-3d card-blue scroll-reveal group">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="icon-blue w-12 h-12 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
<svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
</svg>
</div>
<h3 class="text-base font-bold text-white mb-2" data-i18n="f1_title">Электронная подпись</h3>
<p class="text-sm text-gray-400 leading-relaxed" data-i18n="f1_desc">Подписывайте документы одним кликом. Каждый файл получает уникальный код — его можно проверить за секунду через телефон.</p>
</div>
</div>
<div class="glass rounded-3xl p-6 card-3d card-purple scroll-reveal group" style="transition-delay:0.1s">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="icon-purple w-12 h-12 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
<svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
</svg>
</div>
<h3 class="text-base font-bold text-white mb-2" data-i18n="f2_title">Роли и права</h3>
<p class="text-sm text-gray-400 leading-relaxed" data-i18n="f2_desc">Каждый сотрудник видит только то, что ему нужно. Директор, менеджер, работник — у каждого свои возможности.</p>
</div>
</div>
<div class="glass rounded-3xl p-6 card-3d card-green scroll-reveal group" style="transition-delay:0.2s">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="icon-green w-12 h-12 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
<svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
</svg>
</div>
<h3 class="text-base font-bold text-white mb-2" data-i18n="f3_title">Надёжное хранение</h3>
<p class="text-sm text-gray-400 leading-relaxed" data-i18n="f3_desc">Все документы сохраняются в защищённом хранилище. Ничего не потеряется — история действий всегда под рукой.</p>
</div>
</div>
<div class="glass rounded-3xl p-6 card-3d card-orange scroll-reveal group">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="icon-orange w-12 h-12 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
<svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
</svg>
</div>
<h3 class="text-base font-bold text-white mb-2" data-i18n="f4_title">Умный анализ</h3>
<p class="text-sm text-gray-400 leading-relaxed" data-i18n="f4_desc">Система сама считает, сколько документов обработано, кто подписал, а кто ещё нет. Вся статистика — в одном месте.</p>
</div>
</div>
<div class="glass rounded-3xl p-6 card-3d card-pink scroll-reveal group" style="transition-delay:0.1s">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="icon-pink w-12 h-12 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
<svg class="w-6 h-6 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
</svg>
</div>
<h3 class="text-base font-bold text-white mb-2" data-i18n="f5_title">Все форматы</h3>
<p class="text-sm text-gray-400 leading-relaxed" data-i18n="f5_desc">Работайте с PDF, Word и Excel. Подпись и печать автоматически добавляются в документ — форматирование сохраняется.</p>
</div>
</div>
<div class="glass rounded-3xl p-6 card-3d card-indigo scroll-reveal group" style="transition-delay:0.2s">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="icon-indigo w-12 h-12 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
<svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
</svg>
</div>
<h3 class="text-base font-bold text-white mb-2" data-i18n="f6_title">Удобный дизайн</h3>
<p class="text-sm text-gray-400 leading-relaxed" data-i18n="f6_desc">Тёмная тема бережёт глаза. Всё на своих местах — разобраться сможет каждый, даже без опыта работы с такими системами.</p>
</div>
</div>
</div>
</div>
</section>
<!-- ===================== DEPARTMENTS SECTION (NEW) ===================== -->
<section id="departments" class="relative py-24 overflow-hidden">
<div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="text-center mb-14 scroll-reveal">
<span class="inline-flex items-center gap-2 text-xs font-semibold text-pink-400 uppercase tracking-widest mb-4">
<span class="w-8 h-px bg-pink-400"></span>
<span data-i18n="depts_label">Отделы</span>
<span class="w-8 h-px bg-pink-400"></span>
</span>
<h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white mb-4 tracking-tight" data-i18n="depts_title">Создайте отделы — работа станет легче</h2>
<p class="max-w-2xl mx-auto text-sm sm:text-base text-gray-400" data-i18n="depts_subtitle">У вас есть компания? Создайте отделы и отправляйте им документы — сотрудники обмениваются ими между собой и всё делают правильно.</p>
</div>
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
<!-- Department 1: Management -->
<div class="glass rounded-3xl p-6 card-3d card-blue scroll-reveal group">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="flex items-start justify-between mb-5">
<div class="icon-blue w-12 h-12 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
<svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 17l-1.5-8L8 12l4-7 4 7 5.5-3L20 17H4zM4 20h16"/>
</svg>
</div>
<span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-blue-500/10 border border-blue-500/25 text-blue-400" data-i18n="d1_tag">Полный доступ</span>
</div>
<h3 class="text-base font-bold text-white mb-2" data-i18n="d1_title">Руководство</h3>
<p class="text-sm text-gray-400 leading-relaxed mb-5" data-i18n="d1_desc">Отправляет документы отделам, подписывает финальные версии и видит отчёты по всей компании.</p>
<div class="pt-4 border-t border-white/5 flex items-center justify-between text-xs text-gray-500">
<span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>5 <span data-i18n="dept_roles">ролей</span></span>
<span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>60 <span data-i18n="dept_templates">шаблонов</span></span>
</div>
</div>
</div>
<!-- Department 2: HR -->
<div class="glass rounded-3xl p-6 card-3d card-purple scroll-reveal group" style="transition-delay:0.1s">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="flex items-start justify-between mb-5">
<div class="icon-purple w-12 h-12 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
<svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
</svg>
</div>
<span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-purple-500/10 border border-purple-500/25 text-purple-400" data-i18n="d2_tag">Кадры</span>
</div>
<h3 class="text-base font-bold text-white mb-2" data-i18n="d2_title">HR-отдел</h3>
<p class="text-sm text-gray-400 leading-relaxed mb-5" data-i18n="d2_desc">Приказы, отпуска и трудовые договоры — документы доходят до сотрудников за минуты.</p>
<div class="pt-4 border-t border-white/5 flex items-center justify-between text-xs text-gray-500">
<span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>4 <span data-i18n="dept_roles">ролей</span></span>
<span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>85 <span data-i18n="dept_templates">шаблонов</span></span>
</div>
</div>
</div>
<!-- Department 3: Accounting -->
<div class="glass rounded-3xl p-6 card-3d card-green scroll-reveal group" style="transition-delay:0.2s">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="flex items-start justify-between mb-5">
<div class="icon-green w-12 h-12 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
<svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m-7 4h.01M12 11h.01M15 11h.01M8 15h.01M12 15h.01M15 15h.01M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
</svg>
</div>
<span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-green-500/10 border border-green-500/25 text-green-400" data-i18n="d3_tag">Финансы</span>
</div>
<h3 class="text-base font-bold text-white mb-2" data-i18n="d3_title">Бухгалтерия</h3>
<p class="text-sm text-gray-400 leading-relaxed mb-5" data-i18n="d3_desc">Счета, акты и отчёты — без бумаги и очередей, всё приходит вовремя.</p>
<div class="pt-4 border-t border-white/5 flex items-center justify-between text-xs text-gray-500">
<span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>4 <span data-i18n="dept_roles">ролей</span></span>
<span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>120 <span data-i18n="dept_templates">шаблонов</span></span>
</div>
</div>
</div>
<!-- Department 4: Legal -->
<div class="glass rounded-3xl p-6 card-3d card-orange scroll-reveal group">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="flex items-start justify-between mb-5">
<div class="icon-orange w-12 h-12 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
<svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v18M8 21h8M4 7h16M6 7l-3 6a3 3 0 006 0L6 7zm12 0l-3 6a3 3 0 006 0l-3-6z"/>
</svg>
</div>
<span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-orange-500/10 border border-orange-500/25 text-orange-400" data-i18n="d4_tag">Право</span>
</div>
<h3 class="text-base font-bold text-white mb-2" data-i18n="d4_title">Юридический отдел</h3>
<p class="text-sm text-gray-400 leading-relaxed mb-5" data-i18n="d4_desc">Договоры и согласования — отделы обмениваются документами, ничего не теряется.</p>
<div class="pt-4 border-t border-white/5 flex items-center justify-between text-xs text-gray-500">
<span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>3 <span data-i18n="dept_roles">ролей</span></span>
<span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>70 <span data-i18n="dept_templates">шаблонов</span></span>
</div>
</div>
</div>
<!-- Department 5: Sales -->
<div class="glass rounded-3xl p-6 card-3d card-pink scroll-reveal group" style="transition-delay:0.1s">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="flex items-start justify-between mb-5">
<div class="icon-pink w-12 h-12 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
<svg class="w-6 h-6 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
</svg>
</div>
<span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-pink-500/10 border border-pink-500/25 text-pink-400" data-i18n="d5_tag">Продажи</span>
</div>
<h3 class="text-base font-bold text-white mb-2" data-i18n="d5_title">Отдел продаж</h3>
<p class="text-sm text-gray-400 leading-relaxed mb-5" data-i18n="d5_desc">Коммерческие предложения и контракты — отправляйте клиентам и коллегам в один клик.</p>
<div class="pt-4 border-t border-white/5 flex items-center justify-between text-xs text-gray-500">
<span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>6 <span data-i18n="dept_roles">ролей</span></span>
<span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>95 <span data-i18n="dept_templates">шаблонов</span></span>
</div>
</div>
</div>
<!-- Department 6: Logistics -->
<div class="glass rounded-3xl p-6 card-3d card-cyan scroll-reveal group" style="transition-delay:0.2s">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="flex items-start justify-between mb-5">
<div class="icon-cyan w-12 h-12 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
<svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-8V8a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 01-5.516 0"/>
</svg>
</div>
<span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/25 text-cyan-400" data-i18n="d6_tag">Доставка</span>
</div>
<h3 class="text-base font-bold text-white mb-2" data-i18n="d6_title">Логистика</h3>
<p class="text-sm text-gray-400 leading-relaxed mb-5" data-i18n="d6_desc">Накладные и маршруты — водители и склад подписывают без задержек.</p>
<div class="pt-4 border-t border-white/5 flex items-center justify-between text-xs text-gray-500">
<span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>4 <span data-i18n="dept_roles">ролей</span></span>
<span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>55 <span data-i18n="dept_templates">шаблонов</span></span>
</div>
</div>
</div>
</div>
<!-- Create department note -->
<div class="glass rounded-2xl px-6 py-5 flex flex-col sm:flex-row items-center justify-center sm:justify-between gap-4 scroll-reveal" style="transition-delay:0.25s">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500/20 to-pink-500/20 border border-white/10 flex items-center justify-center flex-shrink-0">
<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
</svg>
</div>
<span class="text-sm text-gray-300 text-center sm:text-left" data-i18n="depts_note">Не нашли свой отдел? Создайте его за 30 секунд — с собственными ролями и шаблонами.</span>
</div>
<a href="/login" class="btn-primary px-5 py-2.5 rounded-xl text-sm font-semibold whitespace-nowrap" data-i18n="depts_note_cta">Создать отдел</a>
</div>
</div>
</section>




<!-- ===================== GEOGRAPHY SECTION (NEW) ===================== -->
<section id="geography" class="relative py-24 overflow-hidden">
<div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="text-center mb-14 scroll-reveal">
<span class="inline-flex items-center gap-2 text-xs font-semibold text-green-400 uppercase tracking-widest mb-4">
<span class="w-8 h-px bg-green-400"></span>
<span data-i18n="geo_label">География</span>
<span class="w-8 h-px bg-green-400"></span>
</span>
<h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white mb-4 tracking-tight" data-i18n="geo_title">Весь Таджикистан — на связи</h2>
<p class="max-w-3xl mx-auto text-sm sm:text-base text-gray-400" data-i18n="geo_subtitle">Здесь добавлены все области и города Таджикистана, чтобы удобно было отправлять документы в любую точку страны.</p>
</div>
<div class="grid lg:grid-cols-5 gap-5 mb-6">
<!-- Map card -->


<div class="lg:col-span-3 glass rounded-3xl p-6 card-3d card-blue scroll-reveal">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="flex items-center justify-between mb-5 flex-wrap gap-2">
<div class="flex items-center gap-2">
<span class="text-lg">🇹🇯</span>
<span class="text-[11px] uppercase tracking-[0.2em] text-gray-500 font-bold" data-i18n="geo_map_title">Карта покрытия</span>
</div>
<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/25 text-[10px] font-bold text-emerald-400">
<span class="pulse-dot" style="background:#10b981;box-shadow:0 0 8px #10b981;width:5px;height:5px;"></span>
<span data-i18n="geo_badge1">Покрытие 100%</span>
</span>
</div>
<div class="relative rounded-2xl bg-white/[0.015] border border-white/5 p-2 sm:p-4">
<svg viewBox="0 0 420 300" class="w-full h-auto">
<defs>
<linearGradient id="tjFill" x1="0" y1="0" x2="1" y2="1">
<stop offset="0%" stop-color="#3b82f6" stop-opacity="0.12"/>
<stop offset="55%" stop-color="#8b5cf6" stop-opacity="0.05"/>
<stop offset="100%" stop-color="#ec4899" stop-opacity="0.09"/>
</linearGradient>
<linearGradient id="tjStroke" x1="0" y1="0" x2="1" y2="1">
<stop offset="0%" stop-color="#3b82f6"/>
<stop offset="50%" stop-color="#8b5cf6"/>
<stop offset="100%" stop-color="#ec4899"/>
</linearGradient>
</defs>
<!-- Country outline (glow + main) — точный контур Таджикистана, построен из реальных географических данных -->
<path d="M 200.49 82.63 Q 182.81 97.53 156.53 93.49 Q 130.25 89.44 127.96 103.36 Q 125.67 117.28 151.85 115.41 Q 178.03 113.54 207.87 121.38 Q 237.70 129.22 283.35 125.56 Q 329.00 121.89 335.12 144.24 Q 341.24 166.58 349.18 164.15 Q 357.11 161.72 371.77 167.22 Q 386.44 172.71 385.60 182.10 Q 384.76 191.49 388.38 205.25 Q 392.00 219.02 367.10 218.98 Q 342.20 218.94 325.57 217.16 Q 308.94 215.38 293.90 226.19 Q 278.85 237.00 268.13 239.40 Q 257.42 241.79 249.01 246.87 Q 240.59 251.94 231.03 244.03 Q 221.47 236.13 223.72 215.84 Q 225.97 195.56 218.67 194.41 Q 211.37 193.26 213.99 185.88 Q 216.62 178.50 203.55 173.01 Q 190.48 167.52 180.08 175.92 Q 169.69 184.32 167.13 194.06 Q 164.58 203.80 160.86 207.34 Q 157.14 210.89 142.71 210.39 Q 128.28 209.89 120.49 220.94 Q 112.70 232.00 104.57 227.33 Q 96.44 222.67 78.96 230.43 Q 61.48 238.18 54.10 235.24 Q 46.73 232.30 60.30 207.86 Q 73.87 183.42 68.65 165.45 Q 63.44 147.47 45.72 141.71 Q 28.00 135.95 34.26 125.32 Q 40.52 114.69 60.68 115.82 Q 80.84 116.96 92.31 103.61 Q 103.79 90.27 111.46 74.77 Q 119.14 59.28 151.42 53.67 Q 183.71 48.06 178.67 59.26 Q 173.64 70.45 177.10 77.16 Q 180.56 83.87 190.52 83.25 Q 200.49 82.63 191.65 90.08 Z"
fill="none" stroke="url(#tjStroke)" stroke-width="5" opacity="0.12"/>
<path d="M 200.49 82.63 Q 182.81 97.53 156.53 93.49 Q 130.25 89.44 127.96 103.36 Q 125.67 117.28 151.85 115.41 Q 178.03 113.54 207.87 121.38 Q 237.70 129.22 283.35 125.56 Q 329.00 121.89 335.12 144.24 Q 341.24 166.58 349.18 164.15 Q 357.11 161.72 371.77 167.22 Q 386.44 172.71 385.60 182.10 Q 384.76 191.49 388.38 205.25 Q 392.00 219.02 367.10 218.98 Q 342.20 218.94 325.57 217.16 Q 308.94 215.38 293.90 226.19 Q 278.85 237.00 268.13 239.40 Q 257.42 241.79 249.01 246.87 Q 240.59 251.94 231.03 244.03 Q 221.47 236.13 223.72 215.84 Q 225.97 195.56 218.67 194.41 Q 211.37 193.26 213.99 185.88 Q 216.62 178.50 203.55 173.01 Q 190.48 167.52 180.08 175.92 Q 169.69 184.32 167.13 194.06 Q 164.58 203.80 160.86 207.34 Q 157.14 210.89 142.71 210.39 Q 128.28 209.89 120.49 220.94 Q 112.70 232.00 104.57 227.33 Q 96.44 222.67 78.96 230.43 Q 61.48 238.18 54.10 235.24 Q 46.73 232.30 60.30 207.86 Q 73.87 183.42 68.65 165.45 Q 63.44 147.47 45.72 141.71 Q 28.00 135.95 34.26 125.32 Q 40.52 114.69 60.68 115.82 Q 80.84 116.96 92.31 103.61 Q 103.79 90.27 111.46 74.77 Q 119.14 59.28 151.42 53.67 Q 183.71 48.06 178.67 59.26 Q 173.64 70.45 177.10 77.16 Q 180.56 83.87 190.52 83.25 Q 200.49 82.63 191.65 90.08 Z"
fill="url(#tjFill)" stroke="url(#tjStroke)" stroke-width="1.5" opacity="0.9"/>
<!-- Delivery routes from Dushanbe -->
<line class="route-line" x1="92.9" y1="164.0" x2="133.4" y2="80.7"  stroke="rgba(59,130,246,0.5)" stroke-width="1.2"/>
<line class="route-line" x1="92.9" y1="164.0" x2="182.1" y2="88.8"  stroke="rgba(139,92,246,0.5)" stroke-width="1.2" style="animation-delay:.6s"/>
<line class="route-line" x1="92.9" y1="164.0" x2="92.6" y2="199.0" stroke="rgba(16,185,129,0.5)" stroke-width="1.2" style="animation-delay:1.2s"/>
<line class="route-line" x1="92.9" y1="164.0" x2="141.2" y2="195.2" stroke="rgba(245,158,11,0.5)" stroke-width="1.2" style="animation-delay:1.8s"/>
<line class="route-line" x1="92.9" y1="164.0" x2="226.4" y2="215.6" stroke="rgba(236,72,153,0.5)" stroke-width="1.2" style="animation-delay:2.4s"/>
<!-- Flying documents -->
<circle r="2.6" fill="#3b82f6">
<animateMotion dur="3.6s" repeatCount="indefinite" path="M92.9 164.0 L133.4 80.7"/>
</circle>
<circle r="2.6" fill="#ec4899">
<animateMotion dur="5s" repeatCount="indefinite" path="M92.9 164.0 L226.4 215.6"/>
</circle>
<circle r="2.2" fill="#10b981">
<animateMotion dur="3s" repeatCount="indefinite" path="M92.9 164.0 L92.6 199.0"/>
</circle>
<!-- Major cities with ping -->
<g>
<circle class="map-ping" cx="133.4" cy="80.7" r="8" fill="rgba(59,130,246,0.35)"/>
<circle cx="133.4" cy="80.7" r="3.4" fill="#3b82f6"/>
<text x="133.4" y="67" text-anchor="middle" class="map-label">Худжанд</text>
</g>
<g>
<circle class="map-ping" cx="182.1" cy="88.8" r="7" fill="rgba(6,182,212,0.3)" style="animation-delay:.8s"/>
<circle cx="182.1" cy="88.8" r="3" fill="#06b6d4"/>
<text x="182.1" y="76" text-anchor="middle" class="map-label">Исфара</text>
</g>
<g>
<circle class="map-ping" cx="92.6" cy="199.0" r="8" fill="rgba(16,185,129,0.3)" style="animation-delay:1.4s"/>
<circle cx="92.6" cy="199.0" r="3.4" fill="#10b981"/>
<text x="92.6" y="215" text-anchor="middle" class="map-label">Бохтар</text>
</g>
<g>
<circle class="map-ping" cx="141.2" cy="195.2" r="8" fill="rgba(245,158,11,0.3)" style="animation-delay:2s"/>
<circle cx="141.2" cy="195.2" r="3.4" fill="#f59e0b"/>
<text x="141.2" y="211" text-anchor="middle" class="map-label">Кулоб</text>
</g>
<g>
<circle class="map-ping" cx="226.4" cy="215.6" r="8" fill="rgba(236,72,153,0.3)" style="animation-delay:2.6s"/>
<circle cx="226.4" cy="215.6" r="3.4" fill="#ec4899"/>
<text x="226.4" y="232" text-anchor="middle" class="map-label">Хорог</text>
</g>
<!-- Capital: Dushanbe -->
<g>
<circle class="map-ping" cx="92.9" cy="164.0" r="11" fill="rgba(59,130,246,0.35)" style="animation-duration:2s"/>
<circle cx="92.9" cy="164.0" r="4.6" fill="#3b82f6" stroke="#bfdbfe" stroke-width="1.2"/>
<text x="92.9" y="184" text-anchor="middle" class="map-label-capital">Душанбе</text>
</g>
</svg>
<!-- Floating badge -->
<div class="absolute bottom-3 left-3 glass rounded-xl px-3 py-2 flex items-center gap-2">
<svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
</svg>
<span class="text-[11px] font-bold text-gray-300" data-i18n="geo_badge2">Доставка за секунды</span>
</div>
</div>
</div>
</div>

<!-- Regions list -->
<div class="lg:col-span-2 flex flex-col gap-3 scroll-reveal" style="transition-delay:0.1s">
<div class="region-card glass rounded-2xl p-4 flex items-center gap-4 flex-1">
<div class="icon-blue w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0">
<svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118L1.977 10.1c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
</svg>
</div>
<div class="min-w-0">
<div class="text-sm font-bold text-white mb-0.5" data-i18n="g1_title">Душанбе</div>
<div class="text-xs text-gray-400 truncate" data-i18n="g1_desc">Столица • все районы города</div>
</div>
</div>
<div class="region-card glass rounded-2xl p-4 flex items-center gap-4 flex-1">
<div class="icon-purple w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0">
<svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
</svg>
</div>
<div class="min-w-0">
<div class="text-sm font-bold text-white mb-0.5" data-i18n="g2_title">Согдийская область</div>
<div class="text-xs text-gray-400 truncate" data-i18n="g2_desc">Худжанд, Истаравшан, Исфара, Канибадам, Пенджикент</div>
</div>
</div>
<div class="region-card glass rounded-2xl p-4 flex items-center gap-4 flex-1">
<div class="icon-green w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0">
<svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
</svg>
</div>
<div class="min-w-0">
<div class="text-sm font-bold text-white mb-0.5" data-i18n="g3_title">Хатлонская область</div>
<div class="text-xs text-gray-400 truncate" data-i18n="g3_desc">Бохтер, Кулоб, Левакант, Дангара</div>
</div>
</div>
<div class="region-card glass rounded-2xl p-4 flex items-center gap-4 flex-1">
<div class="icon-orange w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0">
<svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M15 9h.01M9 13h.01M15 13h.01M9 17h.01M15 17h.01"/>
</svg>
</div>
<div class="min-w-0">
<div class="text-sm font-bold text-white mb-0.5" data-i18n="g4_title">ГБАО</div>
<div class="text-xs text-gray-400 truncate" data-i18n="g4_desc">Хорог, Мургаб, Рушан, Ишкашим</div>
</div>
</div>
<div class="region-card glass rounded-2xl p-4 flex items-center gap-4 flex-1">
<div class="icon-pink w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0">
<svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
</svg>
</div>
<div class="min-w-0">
<div class="text-sm font-bold text-white mb-0.5" data-i18n="g5_title">Районы республиканского подчинения</div>
<div class="text-xs text-gray-400 truncate" data-i18n="g5_desc">Вахдат, Турсунзода, Рудаки, Гиссар</div>
</div>
</div>
</div>
</div>
<!-- Geo stats -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
<div class="stat-card-blue rounded-2xl p-4 text-center card-3d card-blue scroll-reveal">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="stat-num gradient-text"><span class="counter" data-target="5">0</span></div>
<div class="text-[10px] uppercase tracking-[0.2em] text-blue-400 font-bold mt-1.5" data-i18n="geo_stat1">регионов</div>
</div>
</div>
<div class="stat-card-purple rounded-2xl p-4 text-center card-3d card-purple scroll-reveal" style="transition-delay:0.08s">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="stat-num gradient-text"><span class="counter" data-target="60">0</span>+</div>
<div class="text-[10px] uppercase tracking-[0.2em] text-purple-400 font-bold mt-1.5" data-i18n="geo_stat2">районов</div>
</div>
</div>
<div class="stat-card-green rounded-2xl p-4 text-center card-3d card-green scroll-reveal" style="transition-delay:0.16s">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="stat-num gradient-text"><span class="counter" data-target="20">0</span>+</div>
<div class="text-[10px] uppercase tracking-[0.2em] text-green-400 font-bold mt-1.5" data-i18n="geo_stat4">городов</div>
</div>
</div>
<div class="stat-card-orange rounded-2xl p-4 text-center card-3d card-orange scroll-reveal" style="transition-delay:0.24s">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="stat-num gradient-text"><span class="counter" data-target="100">0</span>%</div>
<div class="text-[10px] uppercase tracking-[0.2em] text-orange-400 font-bold mt-1.5" data-i18n="geo_stat3">покрытие</div>
</div>
</div>
</div>
<!-- City chips -->
<div class="glass rounded-3xl p-6 scroll-reveal" style="transition-delay:0.3s">
<div class="flex items-center gap-2 mb-4">
<svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
</svg>
<span class="text-[11px] uppercase tracking-[0.2em] text-gray-500 font-bold" data-i18n="geo_cities_label">Города и районы</span>
</div>
<div class="flex flex-wrap gap-2">
<span class="geo-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full glass text-xs text-gray-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>Душанбе</span>
<span class="geo-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full glass text-xs text-gray-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>Худжанд</span>
<span class="geo-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full glass text-xs text-gray-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>Бохтер</span>
<span class="geo-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full glass text-xs text-gray-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Кулоб</span>
<span class="geo-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full glass text-xs text-gray-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-pink-400"></span>Хорог</span>
<span class="geo-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full glass text-xs text-gray-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>Вахдат</span>
<span class="geo-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full glass text-xs text-gray-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>Турсунзода</span>
<span class="geo-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full glass text-xs text-gray-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>Рудаки</span>
<span class="geo-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full glass text-xs text-gray-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>Гиссар</span>
<span class="geo-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full glass text-xs text-gray-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>Истаравшан</span>
<span class="geo-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full glass text-xs text-gray-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>Пенджикент</span>
<span class="geo-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full glass text-xs text-gray-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>Исфара</span>
<span class="geo-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full glass text-xs text-gray-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>Канибадам</span>
<span class="geo-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full glass text-xs text-gray-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>Левакант</span>
<span class="geo-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full glass text-xs text-gray-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>Дангара</span>
<span class="geo-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full glass text-xs text-gray-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-pink-400"></span>Мургаб</span>
<span class="geo-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full glass text-xs text-gray-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-pink-400"></span>Рушан</span>
<span class="geo-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full glass text-xs text-gray-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-pink-400"></span>Ишкашим</span>
<span class="geo-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full glass text-xs text-gray-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Нурек</span>
<span class="geo-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full glass text-xs text-gray-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>+ <span data-i18n="geo_more">вся страна</span></span>
</div>
</div>
</section>


<!-- Security Section -->
<section id="security" class="relative py-24 overflow-hidden">
<div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="text-center mb-14 scroll-reveal">
<span class="inline-flex items-center gap-2 text-xs font-semibold text-green-400 uppercase tracking-widest mb-4">
<span class="w-8 h-px bg-green-400"></span>
<span data-i18n="security_label">Безопасность</span>
<span class="w-8 h-px bg-green-400"></span>
</span>
<h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white mb-4 tracking-tight" data-i18n="security_title">Ваши данные под защитой</h2>
<p class="max-w-2xl mx-auto text-sm sm:text-base text-gray-400" data-i18n="security_subtitle">Несколько уровней защиты — от входа в систему до каждого документа</p>
</div>
<div class="grid md:grid-cols-2 lg:grid-cols-4 gap-5">
<div class="glass rounded-2xl p-5 card-3d card-blue text-center scroll-reveal">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="relative w-14 h-14 rounded-2xl icon-blue flex items-center justify-center mx-auto mb-4">
<svg class="w-7 h-7 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
</svg>
</div>
<h4 class="text-sm font-bold text-white mb-1.5" data-i18n="sec1_title">Защита входа</h4>
<p class="text-xs text-gray-400 leading-relaxed" data-i18n="sec1_desc">Пароли надёжно зашифрованы. Чужой не войдёт в ваш аккаунт.</p>
</div>
</div>
<div class="glass rounded-2xl p-5 card-3d card-purple text-center scroll-reveal" style="transition-delay:0.1s">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="relative w-14 h-14 rounded-2xl icon-purple flex items-center justify-center mx-auto mb-4">
<svg class="w-7 h-7 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
</svg>
</div>
<h4 class="text-sm font-bold text-white mb-1.5" data-i18n="sec2_title">Мгновенные уведомления</h4>
<p class="text-xs text-gray-400 leading-relaxed" data-i18n="sec2_desc">Вы сразу узнаете, когда документ подписан или требует внимания.</p>
</div>
</div>
<div class="glass rounded-2xl p-5 card-3d card-green text-center scroll-reveal" style="transition-delay:0.2s">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="relative w-14 h-14 rounded-2xl icon-green flex items-center justify-center mx-auto mb-4">
<svg class="w-7 h-7 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
</svg>
</div>
<h4 class="text-sm font-bold text-white mb-1.5" data-i18n="sec3_title">История действий</h4>
<p class="text-xs text-gray-400 leading-relaxed" data-i18n="sec3_desc">Кто, когда и что сделал с документом — всё сохраняется и доступно.</p>
</div>
</div>
<div class="glass rounded-2xl p-5 card-3d card-orange text-center scroll-reveal" style="transition-delay:0.3s">
<div class="card-shine"></div>
<div class="card-border"></div>
<div class="card-inner">
<div class="relative w-14 h-14 rounded-2xl icon-orange flex items-center justify-center mx-auto mb-4">
<svg class="w-7 h-7 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
</svg>
</div>
<h4 class="text-sm font-bold text-white mb-1.5" data-i18n="sec4_title">Резервные копии</h4>
<p class="text-xs text-gray-400 leading-relaxed" data-i18n="sec4_desc">Данные автоматически сохраняются. Даже при сбое ничего не потеряется.</p>
</div>
</div>
</div>
</div>
</section>
<!-- Super Admin Card -->
<section id="admin" class="relative py-24 overflow-hidden">
<div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="superadmin-card rounded-3xl p-8 md:p-10 card-3d scroll-reveal">
<div class="card-shine"></div>
<div class="card-inner">
<div class="flex flex-col md:flex-row items-center gap-6 md:gap-8">
<!-- Avatar -->
<div class="relative flex-shrink-0">
<div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 flex items-center justify-center shadow-2xl shadow-blue-500/20">
<span class="text-3xl font-black text-white">AA</span>
</div>
<div class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-emerald-500 border-2 border-ink-900 flex items-center justify-center shadow-lg">
<svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
</svg>
</div>
</div>
<!-- Content -->
<div class="flex-1 text-center md:text-left">
<div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-white/5 border border-white/10 mb-3">
<span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
<span class="text-[10px] uppercase tracking-widest font-bold text-gray-300" data-i18n="admin_badge">Главный администратор</span>
</div>
<h3 class="text-2xl md:text-3xl font-black text-white mb-1 tracking-tight">Аминов Амир</h3>
<p class="text-sm text-gray-400 mb-4" data-i18n="admin_role">Создатель системы DocSign</p>
<p class="text-sm text-gray-300 leading-relaxed mb-5" data-i18n="admin_desc">Если у вас есть компания и вы хотите стать администратором системы — свяжитесь с главным администратором. Он поможет настроить всё для вашей организации.</p>
<a href="https://t.me/amnvamr" target="_blank" class="inline-flex items-center gap-2 btn-primary px-5 py-2.5 rounded-xl text-sm font-semibold">
<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
<path d="M11.944 0C5.346 0 0 5.346 0 11.944c0 6.597 5.346 11.944 11.944 11.944 6.598 0 11.944-5.347 11.944-11.944C23.888 5.346 18.542 0 11.944 0zM18.17 6.83l-2.113 9.968c-.15.66-.543.824-1.096.515l-3.218-2.373-1.553 1.493c-.17.172-.315.315-.646.315l.23-3.267 5.946-5.372c.258-.23-.056-.358-.401-.13l-7.35 4.628-3.166-1c-.687-.215-.702-.687.143-.1l12.355-4.76c.572-.215 1.07.127.91.892z"/>
</svg>
<span data-i18n="admin_contact">Написать в Telegram</span>
</a>
</div>
</div>
</div>
</div>
</div>
</section>
<!-- Contact CTA -->
<section id="contact" class="relative py-24 overflow-hidden">
<div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
<div class="scroll-reveal">
<h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white mb-4 tracking-tight" data-i18n="contact_title">Готовы начать?</h2>
<p class="max-w-xl mx-auto text-sm sm:text-base text-gray-400 mb-8" data-i18n="contact_subtitle">Присоединяйтесь к компаниям, которые уже используют DocSign</p>
<div class="flex flex-wrap items-center justify-center gap-3">
<a href="/login" class="btn-primary px-6 py-3 rounded-xl text-sm font-semibold shadow-2xl" data-i18n="contact_cta">Начать бесплатно</a>
<a href="https://t.me/amnvamr" target="_blank" class="btn-outline px-6 py-3 rounded-xl text-sm font-semibold text-gray-300 flex items-center gap-2">
<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0C5.346 0 0 5.346 0 11.944c0 6.597 5.346 11.944 11.944 11.944 6.598 0 11.944-5.347 11.944-11.944C23.888 5.346 18.542 0 11.944 0zM18.17 6.83l-2.113 9.968c-.15.66-.543.824-1.096.515l-3.218-2.373-1.553 1.493c-.17.172-.315.315-.646.315l.23-3.267 5.946-5.372c.258-.23-.056-.358-.401-.13l-7.35 4.628-3.166-1c-.687-.215-.702-.687.143-.1l12.355-4.76c.572-.215 1.07.127.91.892z"/></svg>
<span data-i18n="contact_tg">Связаться в Telegram</span>
</a>
</div>
</div>
</div>
</section>
<!-- Footer -->
<footer class="relative border-t border-white/5">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
<div class="grid md:grid-cols-4 gap-8 mb-8">
<div class="md:col-span-2">
<a href="#" class="flex items-center gap-3 mb-4">
<div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 flex items-center justify-center">
<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
</svg>
</div>
<span class="text-lg font-bold text-white">Doc<span class="gradient-accent">Sign</span></span>
</a>
<p class="text-sm text-gray-400 max-w-sm mb-5" data-i18n="footer_desc">Платформа электронного документооборота, которая облегчает работу людей в Таджикистане.</p>
<div class="flex items-center gap-2">
<a href="https://t.me/amnvamr" target="_blank" class="w-9 h-9 rounded-xl glass flex items-center justify-center text-gray-400 hover:text-blue-400 hover:border-blue-500/30 transition-all">
<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0C5.346 0 0 5.346 0 11.944c0 6.597 5.346 11.944 11.944 11.944 6.598 0 11.944-5.347 11.944-11.944C23.888 5.346 18.542 0 11.944 0zM18.17 6.83l-2.113 9.968c-.15.66-.543.824-1.096.515l-3.218-2.373-1.553 1.493c-.17.172-.315.315-.646.315l.23-3.267 5.946-5.372c.258-.23-.056-.358-.401-.13l-7.35 4.628-3.166-1c-.687-.215-.702-.687.143-.1l12.355-4.76c.572-.215 1.07.127.91.892z"/></svg>
</a>
<a href="https://t.me/amnvamr" target="_blank" class="w-9 h-9 rounded-xl glass flex items-center justify-center text-gray-400 hover:text-purple-400 hover:border-purple-500/30 transition-all">
<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 1.17.054 1.805.249 2.227.412.558.217.957.477 1.377.896.42.419.68.818.896 1.377.163.422.358 1.057.412 2.227.058 1.266.07 1.646.07 4.85s-.012 3.584-.07 4.85c-.054 1.17-.249 1.805-.412 2.227-.217.558-.477.957-.896 1.377-.419.42-.818.68-1.377.896-.422.163-1.057.358-2.227.412-1.266.058-1.646.07-4.85.07s-3.584-.012-4.85-.07c-1.17-.054-1.805-.249-2.227-.412-.558-.217-.957-.477-1.377-.896-.419-.42-.68-.818-.896-1.377-.163-.422-.358-1.057-.412-2.227-.058-1.266-.07-1.646-.07-4.85s.012-3.584.07-4.85c.054-1.17.249-1.805.412-2.227.217-.558.477-.957.896-1.377.419-.42.818-.68 1.377-.896.422-.163 1.057-.358 2.227-.412 1.266-.058 1.646-.07 4.85-.07M12 0C8.741 0 8.333.014 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.014 8.333 0 8.741 0 12s.014 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126s1.355 1.078 2.126 1.384c.766.296 1.636.499 2.913.558C8.333 23.986 8.741 24 12 24s3.667-.014 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384s1.078-1.354 1.384-2.126c.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126s-1.354-1.078-2.126-1.384c-.765-.296-1.636-.499-2.913-.558C15.667.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
</a>
</div>
</div>
<div>
<h4 class="text-xs font-semibold text-white uppercase tracking-wider mb-4" data-i18n="footer_product">Продукт</h4>
<ul class="space-y-2 text-sm text-gray-400">
<li><a href="#features" class="hover:text-blue-400 transition-colors" data-i18n="footer_features">Возможности</a></li>
<li><a href="#faceid" class="hover:text-cyan-400 transition-colors" data-i18n="footer_faceid">Face ID</a></li>
<li><a href="#ai" class="hover:text-cyan-400 transition-colors" data-i18n="footer_ai">ИИ / RAG</a></li>
<li><a href="#departments" class="hover:text-pink-400 transition-colors" data-i18n="footer_departments">Отделы</a></li>
<li><a href="#geography" class="hover:text-green-400 transition-colors" data-i18n="footer_geography">География</a></li>
<li><a href="#analytics" class="hover:text-purple-400 transition-colors" data-i18n="footer_analytics">Аналитика</a></li>
<li><a href="#security" class="hover:text-green-400 transition-colors" data-i18n="footer_security">Безопасность</a></li>
</ul>
</div>
<div>
<h4 class="text-xs font-semibold text-white uppercase tracking-wider mb-4" data-i18n="footer_company">Компания</h4>
<ul class="space-y-2 text-sm text-gray-400">
<li><a href="#admin" class="hover:text-orange-400 transition-colors" data-i18n="footer_admin">Администратор</a></li>
<li><a href="#contact" class="hover:text-pink-400 transition-colors" data-i18n="footer_contact">Контакты</a></li>
<li><a href="https://t.me/amnvamr" target="_blank" class="hover:text-blue-400 transition-colors">Telegram</a></li>
</ul>
</div>
</div>
<div class="line-glow mb-6"></div>
<div class="flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-gray-500">
<span>© 2026 DocSign. <span data-i18n="footer_rights">Все права защищены.</span></span>
<span class="flex items-center gap-2">
<span class="text-sm">🇹🇯</span>
<span data-i18n="footer_made">Сделано в Таджикистане</span>
</span>
</div>
</div>
</footer>
</div>
<script>
// Включаем режим JS (анимации появления) — если скрипт упадёт, контент всё равно виден
document.documentElement.classList.add('js');
// ===== 3D BACKGROUND (Three.js) =====
(function() {
const canvas = document.getElementById('three-bg');
const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
renderer.setSize(window.innerWidth, window.innerHeight);
renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
camera.position.z = 5;
// Меньше частиц на мобильных для производительности
const isMobile = window.innerWidth < 768;
const particlesGeometry = new THREE.BufferGeometry();
const particlesCount = isMobile ? 600 : 1500;
const posArray = new Float32Array(particlesCount * 3);
const colorsArray = new Float32Array(particlesCount * 3);
const colors = [
[0.23, 0.51, 0.96],
[0.55, 0.36, 0.96],
[0.93, 0.28, 0.60],
[0.06, 0.73, 0.51],
];
for (let i = 0; i < particlesCount * 3; i += 3) {
posArray[i] = (Math.random() - 0.5) * 15;
posArray[i + 1] = (Math.random() - 0.5) * 15;
posArray[i + 2] = (Math.random() - 0.5) * 15;
const color = colors[Math.floor(Math.random() * colors.length)];
colorsArray[i] = color[0];
colorsArray[i + 1] = color[1];
colorsArray[i + 2] = color[2];
}
particlesGeometry.setAttribute('position', new THREE.BufferAttribute(posArray, 3));
particlesGeometry.setAttribute('color', new THREE.BufferAttribute(colorsArray, 3));
const particlesMaterial = new THREE.PointsMaterial({
size: 0.015,
vertexColors: true,
transparent: true,
opacity: 0.6,
blending: THREE.AdditiveBlending
});
const particlesMesh = new THREE.Points(particlesGeometry, particlesMaterial);
scene.add(particlesMesh);
const group = new THREE.Group();
scene.add(group);
const icoGeo = new THREE.IcosahedronGeometry(1.5, 1);
const icoMat = new THREE.MeshBasicMaterial({
color: 0x3b82f6,
wireframe: true,
transparent: true,
opacity: 0.2
});
const ico = new THREE.Mesh(icoGeo, icoMat);
group.add(ico);
const torusGeo = new THREE.TorusGeometry(2.5, 0.02, 16, 100);
const torusMat = new THREE.MeshBasicMaterial({
color: 0x8b5cf6,
transparent: true,
opacity: 0.3
});
const torus = new THREE.Mesh(torusGeo, torusMat);
torus.rotation.x = Math.PI / 3;
group.add(torus);
const torus2Geo = new THREE.TorusGeometry(3, 0.015, 16, 100);
const torus2Mat = new THREE.MeshBasicMaterial({
color: 0xec4899,
transparent: true,
opacity: 0.2
});
const torus2 = new THREE.Mesh(torus2Geo, torus2Mat);
torus2.rotation.x = -Math.PI / 4;
torus2.rotation.y = Math.PI / 6;
group.add(torus2);
let mouseX = 0, mouseY = 0;
document.addEventListener('mousemove', (e) => {
mouseX = (e.clientX / window.innerWidth) * 2 - 1;
mouseY = (e.clientY / window.innerHeight) * 2 - 1;
});
function animate() {
requestAnimationFrame(animate);
ico.rotation.x += 0.0015;
ico.rotation.y += 0.002;
torus.rotation.z += 0.001;
torus2.rotation.z -= 0.0008;
particlesMesh.rotation.y += 0.0003;
group.rotation.x += (mouseY * 0.1 - group.rotation.x) * 0.02;
group.rotation.y += (mouseX * 0.1 - group.rotation.y) * 0.02;
renderer.render(scene, camera);
}
animate();
window.addEventListener('resize', () => {
camera.aspect = window.innerWidth / window.innerHeight;
camera.updateProjectionMatrix();
renderer.setSize(window.innerWidth, window.innerHeight);
});
})();
// ===== 3D CARD TILT EFFECT =====
document.querySelectorAll('.card-3d').forEach(card => {
card.addEventListener('mousemove', (e) => {
// Отключаем эффект на мобильных
if (window.innerWidth < 768) return;
const rect = card.getBoundingClientRect();
const x = e.clientX - rect.left;
const y = e.clientY - rect.top;
const cx = rect.width / 2;
const cy = rect.height / 2;
const rotateX = ((y - cy) / cy) * -6;
const rotateY = ((x - cx) / cx) * 6;
card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
card.style.setProperty('--mx', `${x}px`);
card.style.setProperty('--my', `${y}px`);
});
card.addEventListener('mouseleave', () => {
card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0)';
});
});
// ===== TRANSLATIONS =====
const translations = {
ru: {
nav_features: "Возможности", nav_faceid: "Face ID", nav_ai: "ИИ", nav_departments: "Отделы", nav_geography: "География", nav_analytics: "Аналитика", nav_security: "Безопасность", nav_admin: "Админ", nav_contact: "Контакты", nav_start: "Начать",
hero_badge: "Система нового поколения",
hero_title_1: "Электронный документооборот", hero_title_2: "Просто", hero_title_3: "Быстро", hero_title_4: "Надёжно",
hero_subtitle: "DocSign — современная платформа для работы с документами. Подписывайте, отправляйте и храните файлы в один клик. Просто и надёжно — для людей, чтобы работа стала легче.",
hero_cta: "Начать работу", hero_learn: "Узнать больше",
stat_sec: "сек", stat_speed: "Скорость", stat_speed_desc: "на один документ",
stat_users: "Пользователи", stat_users_desc: "зарегистрировано",
stat_access: "Доступ", stat_access_desc: "всегда онлайн",
stat_protection: "Защита", stat_protection_desc: "юридическая сила",
faceid_label: "Face ID • Биометрия",
faceid_report_badge: "Краткий отчёт",
faceid_title: "Вход и подпись — по вашему лицу",
faceid_text: "Если у вас есть компания — Face ID станет вашим помощником. Сотрудник смотрит в камеру, система за долю секунды подтверждает личность и подписывает документ. Руководитель получает краткий отчёт: кто, когда и что подписал.",
faceid_b1_t: "Мгновенное подтверждение", faceid_b1_d: "Распознавание лица занимает 0.3 секунды — быстрее любого пароля.",
faceid_b2_t: "Полные отчёты", faceid_b2_d: "Каждая проверка сохраняется: кто вошёл, когда и что подписал.",
faceid_b3_t: "Для всей компании", faceid_b3_d: "Подключите сотрудников, назначьте роли — и работайте без бумаг.",
faceid_cta: "Подключить Face ID", faceid_report: "Смотреть отчёты",
faceid_accuracy: "точность", faceid_speed: "сек",
faceid_demo_note: "Живая демонстрация",
faceid_status_scanning: "Сканирование лица...", faceid_status_ok: "Личность подтверждена",
ai_label: "Искусственный интеллект • RAG",
ai_title: "Умный помощник в каждом документе",
ai_subtitle: "ИИ на базе технологии RAG понимает ваши документы, находит нужную информацию за секунды и отвечает на вопросы — как живой эксперт.",
ai_brain_title: "Нейросеть DocSign",
ai_brain_sub: "RAG • Embeddings • LLM",
ai_brain_online: "Онлайн",
ai_stat_speed: "Ответ",
ai_stat_accuracy: "Точность",
ai_stat_uptime: "Работа",
ai_rag_sub: "RAG • Поиск по базе знаний",
ai_rag_active: "Активен",
ai_step_query: "Запрос",
ai_step_retrieve: "Поиск",
ai_step_generate: "ИИ",
ai_step_answer: "Ответ",
ai_f1_title: "Умный поиск", ai_f1_desc: "ИИ понимает смысл вопроса, а не просто слова. Находит нужный документ даже если вы не помните точное название.",
ai_f2_title: "База знаний", ai_f2_desc: "Все ваши документы превращаются в единую базу знаний. ИИ отвечает на вопросы, ссылаясь на конкретные файлы.",
ai_f3_title: "Генерация ответов", ai_f3_desc: "ИИ читает документы и формулирует чёткие ответы. Вы получаете суть без необходимости читать сотни страниц.",
ai_f4_title: "Только ваши данные", ai_f4_desc: "ИИ работает только с документами вашей компании. Никаких утечек — все данные остаются внутри системы.",
ai_f5_title: "Анализ документов", ai_f5_desc: "ИИ автоматически извлекает ключевые данные: суммы, даты, стороны договора. Экономит часы ручной работы.",
ai_f6_title: "Диалог на вашем языке", ai_f6_desc: "Общайтесь с ИИ на русском, таджикском или английском. Он понимает контекст и отвечает естественно.",
ai_cta_title: "Попробуйте ИИ прямо сейчас",
ai_cta_desc: "Задайте вопрос по вашим документам — получите ответ за секунды со ссылками на источники.",
ai_cta_btn: "Запустить ИИ",
ai_cta_learn: "Узнать больше",
ai_demo_q1: "Какая сумма по договору №45 от 12 марта?",
ai_demo_a1: "По договору №45 от 12 марта 2026 общая сумма составляет 245 000 сомони. Срок оплаты — до 30 апреля.",
ai_demo_src_title: "Источники:",
ai_demo_src1: "Договор №45 от 12.03.2026",
ai_demo_src2: "Счёт-фактура №128",
ai_demo_q2: "Кто подписал акт выполненных работ?",
ai_demo_a2: "Акт выполненных работ подписан директором Аминовым А. и главным бухгалтером Рахимовой М. 15 августа 2026.",
analytics_label: "Аналитика", analytics_title: "Всё под контролем", analytics_subtitle: "Следите за регистрациями, документами и активностью в реальном времени",
analysis_reg_title: "Регистрации", analysis_reg_desc: "пользователей зарегистрировано",
analysis_doc_title: "Документы", analysis_doc_desc: "документов отправлено",
analysis_activity_title: "Активность", analysis_activity_desc: "документов обработано успешно",
donut_period: "В МЕСЯЦ",
donut_live: "Обновлено только что",
donut_center_label: "Документы",
donut_center_sub: "всего",
donut_leg1: "Входящие",
donut_leg2: "Отправленные",
donut_leg3: "Подписанные",
donut_leg4: "В очереди",
chart_label: "Обзор за месяц", chart_title: "Динамика работы системы", chart_reg: "Регистрации", chart_doc: "Документы",
features_label: "Возможности", features_title: "Всё, что вам нужно", features_subtitle: "Простые инструменты для сложной работы с документами",
f1_title: "Электронная подпись", f1_desc: "Подписывайте документы одним кликом. Каждый файл получает уникальный код — его можно проверить за секунду через телефон.",
f2_title: "Роли и права", f2_desc: "Каждый сотрудник видит только то, что ему нужно. Директор, менеджер, работник — у каждого свои возможности.",
f3_title: "Надёжное хранение", f3_desc: "Все документы сохраняются в защищённом хранилище. Ничего не потеряется — история действий всегда под рукой.",
f4_title: "Умный анализ", f4_desc: "Система сама считает, сколько документов обработано, кто подписал, а кто ещё нет. Вся статистика — в одном месте.",
f5_title: "Все форматы", f5_desc: "Работайте с PDF, Word и Excel. Подпись и печать автоматически добавляются в документ — форматирование сохраняется.",
f6_title: "Удобный дизайн", f6_desc: "Тёмная тема бережёт глаза. Всё на своих местах — разобраться сможет каждый, даже без опыта работы с такими системами.",
depts_label: "Отделы",
depts_title: "Создайте отделы — работа станет легче",
depts_subtitle: "У вас есть компания? Создайте отделы и отправляйте им документы — сотрудники обмениваются ими между собой и всё делают правильно.",
d1_tag: "Полный доступ", d1_title: "Руководство", d1_desc: "Отправляет документы отделам, подписывает финальные версии и видит отчёты по всей компании.",
d2_tag: "Кадры", d2_title: "HR-отдел", d2_desc: "Приказы, отпуска и трудовые договоры — документы доходят до сотрудников за минуты.",
d3_tag: "Финансы", d3_title: "Бухгалтерия", d3_desc: "Счета, акты и отчёты — без бумаги и очередей, всё приходит вовремя.",
d4_tag: "Право", d4_title: "Юридический отдел", d4_desc: "Договоры и согласования — отделы обмениваются документами, ничего не теряется.",
d5_tag: "Продажи", d5_title: "Отдел продаж", d5_desc: "Коммерческие предложения и контракты — отправляйте клиентам и коллегам в один клик.",
d6_tag: "Доставка", d6_title: "Логистика", d6_desc: "Накладные и маршруты — водители и склад подписывают без задержек.",
dept_roles: "ролей", dept_templates: "шаблонов",
depts_note: "Не нашли свой отдел? Создайте его за 30 секунд — с собственными ролями и шаблонами.",
depts_note_cta: "Создать отдел",
geo_label: "География",
geo_title: "Весь Таджикистан — на связи",
geo_subtitle: "Здесь добавлены все области и города Таджикистана, чтобы удобно было отправлять документы в любую точку страны.",
geo_map_title: "Карта покрытия",
geo_badge1: "Покрытие 100%", geo_badge2: "Доставка за секунды",
g1_title: "Душанбе", g1_desc: "Столица • все районы города",
g2_title: "Согдийская область", g2_desc: "Худжанд, Истаравшан, Исфара, Канибадам, Пенджикент",
g3_title: "Хатлонская область", g3_desc: "Бохтер, Кулоб, Левакант, Дангара",
g4_title: "ГБАО", g4_desc: "Хорог, Мургаб, Рушан, Ишкашим",
g5_title: "Районы республиканского подчинения", g5_desc: "Вахдат, Турсунзода, Рудаки, Гиссар",
geo_stat1: "регионов", geo_stat2: "районов", geo_stat4: "городов", geo_stat3: "покрытие",
geo_cities_label: "Города и районы", geo_more: "вся страна",
security_label: "Безопасность", security_title: "Ваши данные под защитой", security_subtitle: "Несколько уровней защиты — от входа в систему до каждого документа",
sec1_title: "Защита входа", sec1_desc: "Пароли надёжно зашифрованы. Чужой не войдёт в ваш аккаунт.",
sec2_title: "Мгновенные уведомления", sec2_desc: "Вы сразу узнаете, когда документ подписан или требует внимания.",
sec3_title: "История действий", sec3_desc: "Кто, когда и что сделал с документом — всё сохраняется и доступно.",
sec4_title: "Резервные копии", sec4_desc: "Данные автоматически сохраняются. Даже при сбое ничего не потеряется.",
admin_badge: "Главный администратор", admin_role: "Создатель системы DocSign",
admin_desc: "Если у вас есть компания и вы хотите стать администратором системы — свяжитесь с главным администратором. Он поможет настроить всё для вашей организации.",
admin_contact: "Написать в Telegram",
contact_title: "Готовы начать?", contact_subtitle: "Присоединяйтесь к компаниям, которые уже используют DocSign",
contact_cta: "Начать бесплатно", contact_tg: "Связаться в Telegram",
footer_desc: "Платформа электронного документооборота, которая облегчает работу людей в Таджикистане.",
footer_product: "Продукт", footer_features: "Возможности", footer_faceid: "Face ID", footer_ai: "ИИ / RAG", footer_departments: "Отделы", footer_geography: "География", footer_analytics: "Аналитика", footer_security: "Безопасность",
footer_company: "Компания", footer_admin: "Администратор", footer_contact: "Контакты",
footer_rights: "Все права защищены.", footer_made: "Сделано в Таджикистане"
},
en: {
nav_features: "Features", nav_faceid: "Face ID", nav_ai: "AI", nav_departments: "Departments", nav_geography: "Geography", nav_analytics: "Analytics", nav_security: "Security", nav_admin: "Admin", nav_contact: "Contact", nav_start: "Get Started",
hero_badge: "Next-generation system",
hero_title_1: "Electronic document management", hero_title_2: "Simple", hero_title_3: "Fast", hero_title_4: "Reliable",
hero_subtitle: "DocSign is a modern platform for working with documents. Sign, send and store files in one click. Simple and reliable — for people, to make work easier.",
hero_cta: "Get Started", hero_learn: "Learn More",
stat_sec: "sec", stat_speed: "Speed", stat_speed_desc: "per document",
stat_users: "Users", stat_users_desc: "registered",
stat_access: "Access", stat_access_desc: "always online",
stat_protection: "Protection", stat_protection_desc: "legal validity",
faceid_label: "Face ID • Biometrics",
faceid_report_badge: "Quick report",
faceid_title: "Sign in and sign — with your face",
faceid_text: "If you have a company — Face ID becomes your assistant. An employee looks at the camera, the system verifies identity in a fraction of a second and signs the document. The manager gets a brief report: who, when and what was signed.",
faceid_b1_t: "Instant verification", faceid_b1_d: "Face recognition takes 0.3 seconds — faster than any password.",
faceid_b2_t: "Complete reports", faceid_b2_d: "Every check is saved: who logged in, when and what they signed.",
faceid_b3_t: "For the whole company", faceid_b3_d: "Add employees, assign roles — and work paper-free.",
faceid_cta: "Enable Face ID", faceid_report: "View reports",
faceid_accuracy: "accuracy", faceid_speed: "sec",
faceid_demo_note: "Live demo",
faceid_status_scanning: "Scanning face...", faceid_status_ok: "Identity verified",
ai_label: "Artificial Intelligence • RAG",
ai_title: "Smart assistant in every document",
ai_subtitle: "AI powered by RAG technology understands your documents, finds the right information in seconds and answers questions — like a live expert.",
ai_brain_title: "DocSign Neural Network",
ai_brain_sub: "RAG • Embeddings • LLM",
ai_brain_online: "Online",
ai_stat_speed: "Response",
ai_stat_accuracy: "Accuracy",
ai_stat_uptime: "Uptime",
ai_rag_sub: "RAG • Knowledge base search",
ai_rag_active: "Active",
ai_step_query: "Query",
ai_step_retrieve: "Search",
ai_step_generate: "AI",
ai_step_answer: "Answer",
ai_f1_title: "Smart Search", ai_f1_desc: "AI understands the meaning of the question, not just words. Finds the right document even if you don't remember the exact title.",
ai_f2_title: "Knowledge Base", ai_f2_desc: "All your documents become a single knowledge base. AI answers questions by referencing specific files.",
ai_f3_title: "Answer Generation", ai_f3_desc: "AI reads documents and formulates clear answers. You get the essence without having to read hundreds of pages.",
ai_f4_title: "Only your data", ai_f4_desc: "AI works only with your company's documents. No leaks — all data stays inside the system.",
ai_f5_title: "Document Analysis", ai_f5_desc: "AI automatically extracts key data: amounts, dates, contract parties. Saves hours of manual work.",
ai_f6_title: "Dialog in your language", ai_f6_desc: "Chat with AI in Russian, Tajik or English. It understands context and responds naturally.",
ai_cta_title: "Try AI right now",
ai_cta_desc: "Ask a question about your documents — get an answer in seconds with links to sources.",
ai_cta_btn: "Launch AI",
ai_cta_learn: "Learn more",
ai_demo_q1: "What is the amount for contract #45 dated March 12?",
ai_demo_a1: "Contract #45 dated March 12, 2026 has a total amount of 245,000 somoni. Payment deadline — by April 30.",
ai_demo_src_title: "Sources:",
ai_demo_src1: "Contract #45 dated 12.03.2026",
ai_demo_src2: "Invoice #128",
ai_demo_q2: "Who signed the certificate of work performed?",
ai_demo_a2: "The certificate of work performed was signed by Director Aminov A. and Chief Accountant Rakhimova M. on August 15, 2026.",
analytics_label: "Analytics", analytics_title: "Everything under control", analytics_subtitle: "Track registrations, documents and activity in real time",
analysis_reg_title: "Registrations", analysis_reg_desc: "users registered",
analysis_doc_title: "Documents", analysis_doc_desc: "documents sent",
analysis_activity_title: "Activity", analysis_activity_desc: "documents processed successfully",
donut_period: "PER MONTH",
donut_live: "Updated just now",
donut_center_label: "Documents",
donut_center_sub: "total",
donut_leg1: "Incoming",
donut_leg2: "Sent",
donut_leg3: "Signed",
donut_leg4: "In queue",
chart_label: "Monthly overview", chart_title: "System performance", chart_reg: "Registrations", chart_doc: "Documents",
features_label: "Features", features_title: "Everything you need", features_subtitle: "Simple tools for complex document work",
f1_title: "Digital Signature", f1_desc: "Sign documents with one click. Each file gets a unique code — it can be verified in a second via phone.",
f2_title: "Roles & Permissions", f2_desc: "Each employee sees only what they need. Director, manager, worker — each has their own capabilities.",
f3_title: "Reliable Storage", f3_desc: "All documents are stored in secure storage. Nothing will be lost — action history is always at hand.",
f4_title: "Smart Analysis", f4_desc: "The system counts how many documents are processed, who signed and who hasn't. All statistics in one place.",
f5_title: "All Formats", f5_desc: "Work with PDF, Word and Excel. Signature and stamp are automatically added to the document — formatting is preserved.",
f6_title: "Comfortable Design", f6_desc: "Dark theme is easy on the eyes. Everything is in place — anyone can figure it out, even without experience.",
depts_label: "Departments",
depts_title: "Create departments — work becomes easier",
depts_subtitle: "Do you have a company? Create departments and send them documents — employees exchange documents with each other and do everything right.",
d1_tag: "Full access", d1_title: "Management", d1_desc: "Sends documents to departments, signs final versions and sees company-wide reports.",
d2_tag: "HR", d2_title: "HR Department", d2_desc: "Orders, vacations and employment contracts — documents reach employees in minutes.",
d3_tag: "Finance", d3_title: "Accounting", d3_desc: "Invoices, statements and reports — no paper and no queues, everything arrives on time.",
d4_tag: "Legal", d4_title: "Legal Department", d4_desc: "Contracts and approvals — departments exchange documents and nothing gets lost.",
d5_tag: "Sales", d5_title: "Sales Department", d5_desc: "Proposals and contracts — send to clients and colleagues in one click.",
d6_tag: "Delivery", d6_title: "Logistics", d6_desc: "Waybills and routes — drivers and warehouse sign without delays.",
dept_roles: "roles", dept_templates: "templates",
depts_note: "Can't find your department? Create it in 30 seconds — with its own roles and templates.",
depts_note_cta: "Create department",
geo_label: "Geography",
geo_title: "All of Tajikistan — connected",
geo_subtitle: "All regions and cities of Tajikistan are added here, so sending documents anywhere in the country is convenient.",
geo_map_title: "Coverage map",
geo_badge1: "100% coverage", geo_badge2: "Delivery in seconds",
g1_title: "Dushanbe", g1_desc: "Capital • all city districts",
g2_title: "Sughd Region", g2_desc: "Khujand, Istaravshan, Isfara, Kanibadam, Panjakent",
g3_title: "Khatlon Region", g3_desc: "Bokhtar, Kulob, Levakant, Dangara",
g4_title: "GBAO", g4_desc: "Khorog, Murghab, Rushan, Ishkashim",
g5_title: "Districts of Republican Subordination", g5_desc: "Vahdat, Tursunzoda, Rudaki, Hisor",
geo_stat1: "regions", geo_stat2: "districts", geo_stat4: "cities", geo_stat3: "coverage",
geo_cities_label: "Cities & districts", geo_more: "whole country",
security_label: "Security", security_title: "Your data is protected", security_subtitle: "Multiple levels of protection — from login to every document",
sec1_title: "Login Protection", sec1_desc: "Passwords are securely encrypted. No one else can access your account.",
sec2_title: "Instant Notifications", sec2_desc: "You'll know immediately when a document is signed or needs attention.",
sec3_title: "Action History", sec3_desc: "Who, when and what did with the document — everything is saved and accessible.",
sec4_title: "Backups", sec4_desc: "Data is saved automatically. Even in case of failure, nothing will be lost.",
admin_badge: "Chief Administrator", admin_role: "Creator of DocSign system",
admin_desc: "If you have a company and want to become a system administrator — contact the chief administrator. He will help set everything up for your organization.",
admin_contact: "Write on Telegram",
contact_title: "Ready to start?", contact_subtitle: "Join companies already using DocSign",
contact_cta: "Start for free", contact_tg: "Contact on Telegram",
footer_desc: "Electronic document management platform that makes work easier for people in Tajikistan.",
footer_product: "Product", footer_features: "Features", footer_faceid: "Face ID", footer_ai: "AI / RAG", footer_departments: "Departments", footer_geography: "Geography", footer_analytics: "Analytics", footer_security: "Security",
footer_company: "Company", footer_admin: "Administrator", footer_contact: "Contact",
footer_rights: "All rights reserved.", footer_made: "Made in Tajikistan"
},
tj: {
nav_features: "Имкониятҳо", nav_faceid: "Face ID", nav_ai: "Зеҳн", nav_departments: "Шуъбаҳо", nav_geography: "Ҷуғрофия", nav_analytics: "Таҳлил", nav_security: "Амният", nav_admin: "Админ", nav_contact: "Тамос", nav_start: "Оғоз",
hero_badge: "Системаи насли нав",
hero_title_1: "Идоракунии электронии ҳуҷҷатҳо", hero_title_2: "Содда", hero_title_3: "Тез", hero_title_4: "Боэътимод",
hero_subtitle: "DocSign — платформаи муосир барои кор бо ҳуҷҷатҳо. Имзо кунед, фиристед ва файлҳоро дар як клик нигоҳ доред. Содда ва боэътимод — барои одамон, то кор осонтар шавад.",
hero_cta: "Оғоз кунед", hero_learn: "Бештар донед",
stat_sec: "сония", stat_speed: "Суръат", stat_speed_desc: "барои як ҳуҷҷат",
stat_users: "Корбарон", stat_users_desc: "ба қайд гирифта шуданд",
stat_access: "Дастрасӣ", stat_access_desc: "ҳамеша онлайн",
stat_protection: "Ҳимоя", stat_protection_desc: "қувваи ҳуқуқӣ",
faceid_label: "Face ID • Биометрия",
faceid_report_badge: "Ҳисоботи кӯтоҳ",
faceid_title: "Воридшавӣ ва имзо — бо чеҳраи шумо",
faceid_text: "Агар шумо ширкат дошта бошед — Face ID ёвари шумо мешавад. Корманд ба камера нигоҳ мекунад, система дар як лаҳза шахсиятро тасдиқ мекунад ва ҳуҷҷатро имзо мекунад. Роҳбар ҳисоботи кӯтоҳ мегирад: кӣ, кай ва чиро имзо кард.",
faceid_b1_t: "Тасдиқи фаврӣ", faceid_b1_d: "Шиносоии чеҳра 0.3 сония мекашад — тезтар аз ҳар гуна парол.",
faceid_b2_t: "Ҳисоботҳои пурра", faceid_b2_d: "Ҳар як санҷиш нигоҳ дошта мешавад: кӣ ворид шуд, кай ва чиро имзо кард.",
faceid_b3_t: "Барои тамоми ширкат", faceid_b3_d: "Кормандонро пайваст кунед, нақшҳоро таъин кунед — бе коғаз кор кунед.",
faceid_cta: "Face ID-ро пайваст кунед", faceid_report: "Ҳисоботҳоро бинед",
faceid_accuracy: "дақиқӣ", faceid_speed: "сония",
faceid_demo_note: "Намоиши зинда",
faceid_status_scanning: "Сканкунии чеҳра...", faceid_status_ok: "Шахсият тасдиқ шуд",
ai_label: "Зеҳни сунъӣ • RAG",
ai_title: "Ёвари ҳушманд дар ҳар як ҳуҷҷат",
ai_subtitle: "Зеҳни сунъӣ дар асоси технологияи RAG ҳуҷҷатҳои шуморо мефаҳмад, маълумоти лозимиро дар чанд сония меёбад ва ба саволҳо ҷавоб медиҳад — мисли мутахассиси зинда.",
ai_brain_title: "Шабакаи асабии DocSign",
ai_brain_sub: "RAG • Embeddings • LLM",
ai_brain_online: "Онлайн",
ai_stat_speed: "Ҷавоб",
ai_stat_accuracy: "Дақиқӣ",
ai_stat_uptime: "Кор",
ai_rag_sub: "RAG • Ҷустуҷӯ дар пойгоҳи дониш",
ai_rag_active: "Фаъол",
ai_step_query: "Дархост",
ai_step_retrieve: "Ҷустуҷӯ",
ai_step_generate: "Зеҳн",
ai_step_answer: "Ҷавоб",
ai_f1_title: "Ҷустуҷӯи ҳушманд", ai_f1_desc: "Зеҳни сунъӣ маънои саволро мефаҳмад, на танҳо калимаҳоро. Ҳуҷҷати лозимиро меёбад, ҳатто агар номи дақиқро дар ёд надошта бошед.",
ai_f2_title: "Пойгоҳи дониш", ai_f2_desc: "Ҳамаи ҳуҷҷатҳои шумо ба пойгоҳи ягонаи дониш табдил меёбанд. Зеҳни сунъӣ ба саволҳо бо истинод ба файлҳои мушаххас ҷавоб медиҳад.",
ai_f3_title: "Эҷоди ҷавобҳо", ai_f3_desc: "Зеҳни сунъӣ ҳуҷҷатҳоро мехонад ва ҷавобҳои равшан медиҳад. Шумо моҳиятро бе хондани садҳо саҳифа мегиред.",
ai_f4_title: "Танҳо маълумоти шумо", ai_f4_desc: "Зеҳни сунъӣ танҳо бо ҳуҷҷатҳои ширкати шумо кор мекунад. Ҳеҷ гуна ихроҷ — ҳамаи маълумот дар дохили система мемонад.",
ai_f5_title: "Таҳлили ҳуҷҷатҳо", ai_f5_desc: "Зеҳни сунъӣ ба таври худкор маълумоти калидиро мегирад: маблағҳо, санаҳо, тарафҳои шартнома. Соатҳои кори дастиро сарфа мекунад.",
ai_f6_title: "Муколама бо забони шумо", ai_f6_desc: "Бо зеҳни сунъӣ бо забони русӣ, тоҷикӣ ё англисӣ сӯҳбат кунед. Вазъиятро мефаҳмад ва табиӣ ҷавоб медиҳад.",
ai_cta_title: "Зеҳни сунъиро ҳозир санҷед",
ai_cta_desc: "Дар бораи ҳуҷҷатҳои худ савол диҳед — дар чанд сония ҷавоб бо истинод ба манбаъҳо гиред.",
ai_cta_btn: "Зеҳнро оғоз кунед",
ai_cta_learn: "Бештар донед",
ai_demo_q1: "Маблағи шартномаи №45 аз 12 март чанд аст?",
ai_demo_a1: "Мувофиқи шартномаи №45 аз 12 марти 2026 маблағи умумӣ 245 000 сомонӣ аст. Мӯҳлати пардохт — то 30 апрел.",
ai_demo_src_title: "Манбаъҳо:",
ai_demo_src1: "Шартномаи №45 аз 12.03.2026",
ai_demo_src2: "Ҳисоб-фактураи №128",
ai_demo_q2: "Кӣ далолатномаи иҷрои корро имзо кард?",
ai_demo_a2: "Далолатномаи иҷрои кор аз ҷониби директор Аминов А. ва сармуҳосиб Раҳимова М. 15 августи 2026 имзо шудааст.",
analytics_label: "Таҳлил", analytics_title: "Ҳама чиз зери назорат", analytics_subtitle: "Бақайдгириҳо, ҳуҷҷатҳо ва фаъолиятро дар вақти воқеӣ пайгирӣ кунед",
analysis_reg_title: "Бақайдгириҳо", analysis_reg_desc: "корбарон ба қайд гирифта шуданд",
analysis_doc_title: "Ҳуҷҷатҳо", analysis_doc_desc: "ҳуҷҷатҳо фиристода шуданд",
analysis_activity_title: "Фаъолият", analysis_activity_desc: "ҳуҷҷатҳо бомуваффақият коркард шуданд",
donut_period: "ДАР МОҲ",
donut_live: "Ҳозир навсозӣ шуд",
donut_center_label: "Ҳуҷҷатҳо",
donut_center_sub: "ҳамагӣ",
donut_leg1: "Воридотӣ",
donut_leg2: "Содиротӣ",
donut_leg3: "Имзошуда",
donut_leg4: "Дар навбат",
chart_label: "Баррасии моҳона", chart_title: "Динамикаи кори система", chart_reg: "Бақайдгириҳо", chart_doc: "Ҳуҷҷатҳо",
features_label: "Имкониятҳо", features_title: "Ҳама чизе, ки лозим аст", features_subtitle: "Асбобҳои содда барои кори мураккаб бо ҳуҷҷатҳо",
f1_title: "Имзои электронӣ", f1_desc: "Ҳуҷҷатҳоро бо як клик имзо кунед. Ҳар як файл рамзи беназир мегирад — онро тавассути телефон дар як сония санҷидан мумкин аст.",
f2_title: "Нақшҳо ва ҳуқуқҳо", f2_desc: "Ҳар як корманд танҳо он чизеро мебинад, ки ба ӯ лозим аст. Директор, менеджер, коргар — ҳар кадоме имкониятҳои худро дорад.",
f3_title: "Нигоҳдории боэътимод", f3_desc: "Ҳамаи ҳуҷҷатҳо дар анбори ҳифзшуда нигоҳ дошта мешаванд. Ҳеҷ чиз гум намешавад — таърихи амалҳо ҳамеша дар даст аст.",
f4_title: "Таҳлили ҳушманд", f4_desc: "Система худ мешуморад, ки чанд ҳуҷҷат коркард шудааст, кӣ имзо кардааст ва кӣ ҳанӯз не. Ҳамаи омор дар як ҷо.",
f5_title: "Ҳамаи форматҳо", f5_desc: "Бо PDF, Word ва Excel кор кунед. Имзо ва муҳр ба таври худкор ба ҳуҷҷат илова карда мешаванд — формат нигоҳ дошта мешавад.",
f6_title: "Тарҳи қулай", f6_desc: "Мавзӯи торик чашмро хаста намекунад. Ҳама чиз дар ҷояш аст — ҳар кас, ҳатто бе таҷриба, метавонад фаҳмад.",
depts_label: "Шуъбаҳо",
depts_title: "Шуъбаҳоро эҷод кунед — кор осонтар мешавад",
depts_subtitle: "Ширкат доред? Шуъбаҳоро эҷод кунед ва ба онҳо ҳуҷҷат фиристед — кормандон байни худ ҳуҷҷатҳоро мубодила мекунанд ва ҳама чизро дуруст иҷро мекунанд.",
d1_tag: "Дастрасии пурра", d1_title: "Роҳбарият", d1_desc: "Ба шуъбаҳо ҳуҷҷат мефиристад, версияҳои ниҳоиро имзо мекунад ва ҳисоботҳои тамоми ширкатро мебинад.",
d2_tag: "Кадрҳо", d2_title: "Шуъбаи кадрҳо", d2_desc: "Фармонҳо, рухсатиҳо ва шартномаҳои меҳнатӣ — ҳуҷҷатҳо ба кормандон дар дақиқаҳо мерасанд.",
d3_tag: "Молия", d3_title: "Баҳисобгирӣ", d3_desc: "Ҳисобҳо, актҳо ва ҳисоботҳо — бе коғаз ва бе навбат, ҳама чиз саривақт мерасад.",
d4_tag: "Ҳуқуқ", d4_title: "Шуъбаи ҳуқуқӣ", d4_desc: "Шартномаҳо ва мувофиқаҳо — шуъбаҳо ҳуҷҷатҳоро мубодила мекунанд ва ҳеҷ чиз гум намешавад.",
d5_tag: "Фурӯш", d5_title: "Шуъбаи фурӯш", d5_desc: "Пешниҳодҳои тиҷорӣ ва шартномаҳо — ба муштариён ва ҳамкорон дар як клик фиристед.",
d6_tag: "Интиқол", d6_title: "Логистика", d6_desc: "Ҳуҷҷатҳои мол ва маршрутҳо — ронандагон ва анбор бе таъхир имзо мекунанд.",
dept_roles: "нақшҳо", dept_templates: "қолибҳо",
depts_note: "Шуъбаи худро наёфтед? Онро дар 30 сония эҷод кунед — бо нақшҳо ва қолибҳои худ.",
depts_note_cta: "Эҷод кардани шуъба",
geo_label: "Ҷуғрофия",
geo_title: "Тамоми Тоҷикистон — дар алоқа",
geo_subtitle: "Дар ин ҷо тамоми вилоятҳо ва шаҳрҳои Тоҷикистон илова карда шудаанд, то фиристодани ҳуҷҷатҳо ба ҳар нуқтаи кишвар қулай бошад.",
geo_map_title: "Харитаи фарогирӣ",
geo_badge1: "Фарогирӣ 100%", geo_badge2: "Интиқол дар сонияҳо",
g1_title: "Душанбе", g1_desc: "Пойтахт • тамоми ноҳияҳои шаҳар",
g2_title: "Вилояти Суғд", g2_desc: "Хуҷанд, Истаравшан, Исфара, Конибодом, Панҷакент",
g3_title: "Вилояти Хатлон", g3_desc: "Бохтар, Кӯлоб, Левакант, Данғара",
g4_title: "ВМКБ", g4_desc: "Хоруғ, Мурғоб, Рушон, Ишкошим",
g5_title: "Ноҳияҳои тобеи ҷумҳурӣ", g5_desc: "Ваҳдат, Турсунзода, Рӯдакӣ, Ҳисор",
geo_stat1: "вилоят", geo_stat2: "ноҳия", geo_stat4: "шаҳр", geo_stat3: "фарогирӣ",
geo_cities_label: "Шаҳрҳо ва ноҳияҳо", geo_more: "тамоми кишвар",
security_label: "Амният", security_title: "Маълумоти шумо дар ҳимоя", security_subtitle: "Чанд сатҳи ҳимоя — аз воридшавӣ то ҳар як ҳуҷҷат",
sec1_title: "Ҳимояи воридшавӣ", sec1_desc: "Паролҳо боэътимод рамзгузорӣ шудаанд. Ғайриҳо ба ҳисоби шумо ворид шуда наметавонад.",
sec2_title: "Огоҳиҳои фаврӣ", sec2_desc: "Шумо фавран мефаҳмед, ки ҳуҷҷат имзо шудааст ё ба диққат ниёз дорад.",
sec3_title: "Таърихи амалҳо", sec3_desc: "Кӣ, кай ва чӣ бо ҳуҷҷат кард — ҳама нигоҳ дошта мешавад ва дастрас аст.",
sec4_title: "Нусхаҳои эҳтиётӣ", sec4_desc: "Маълумот ба таври худкор нигоҳ дошта мешавад. Ҳатто дар ҳолати вайроншавӣ ҳеҷ чиз гум намешавад.",
admin_badge: "Маъмури асосӣ", admin_role: "Созандаи системаи DocSign",
admin_desc: "Агар шумо ширкат дошта бошед ва хоҳед маъмури система шавед — бо маъмури асосӣ тамос гиред. Ӯ ба шумо барои ташкилотатон кӯмак мекунад.",
admin_contact: "Дар Telegram нависед",
contact_title: "Омодаед оғоз кунед?", contact_subtitle: "Ба ширкатҳое, ки аллакай DocSign-ро истифода мебаранд, ҳамроҳ шавед",
contact_cta: "Ройгон оғоз кунед", contact_tg: "Дар Telegram тамос гиред",
footer_desc: "Платформаи идоракунии электронии ҳуҷҷатҳо, ки кори одамонро дар Тоҷикистон осон мекунад.",
footer_product: "Маҳсулот", footer_features: "Имкониятҳо", footer_faceid: "Face ID", footer_ai: "Зеҳн / RAG", footer_departments: "Шуъбаҳо", footer_geography: "Ҷуғрофия", footer_analytics: "Таҳлил", footer_security: "Амният",
footer_company: "Ширкат", footer_admin: "Маъмур", footer_contact: "Тамос",
footer_rights: "Ҳамаи ҳуқуқҳо ҳифз шудаанд.", footer_made: "Дар Тоҷикистон сохта шудааст"
}
};
let currentLang = 'ru';
function setLanguage(lang) {
currentLang = lang;
const t = translations[lang];
if (!t) return;
document.querySelectorAll('[data-i18n]').forEach(el => {
const key = el.getAttribute('data-i18n');
if (t[key]) el.textContent = t[key];
});
const desktopSelect = document.getElementById('lang-select');
const mobileSelect = document.getElementById('lang-select-mobile');
if (desktopSelect) desktopSelect.value = lang;
if (mobileSelect) mobileSelect.value = lang;
document.documentElement.lang = lang;
localStorage.setItem('docsign_lang', lang);
// Уведомляем динамические элементы (Face ID статус + RAG демо)
document.dispatchEvent(new CustomEvent('docsign-lang-change'));
}
const langSelectDesktop = document.getElementById('lang-select');
const langSelectMobile = document.getElementById('lang-select-mobile');
if (langSelectDesktop) {
langSelectDesktop.addEventListener('change', function() {
setLanguage(this.value);
});
}
if (langSelectMobile) {
langSelectMobile.addEventListener('change', function() {
setLanguage(this.value);
});
}
const savedLang = localStorage.getItem('docsign_lang') || 'ru';
setLanguage(savedLang);
// Navbar scroll
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
if (window.scrollY > 50) {
navbar.classList.add('glass-strong', 'shadow-lg', 'shadow-black/30');
} else {
navbar.classList.remove('glass-strong', 'shadow-lg', 'shadow-black/30');
}
});
// Mobile menu
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const mobileMenu = document.getElementById('mobileMenu');
mobileMenuBtn.addEventListener('click', () => mobileMenu.classList.toggle('hidden'));
mobileMenu.querySelectorAll('a').forEach(link => {
link.addEventListener('click', () => mobileMenu.classList.add('hidden'));
});
// Scroll reveal
const observer = new IntersectionObserver((entries) => {
entries.forEach(entry => {
if (entry.isIntersecting) entry.target.classList.add('revealed');
});
}, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
document.querySelectorAll('.scroll-reveal').forEach(el => observer.observe(el));
// Counter animation
function animateCounter(el, target, duration = 2000) {
const startTime = performance.now();
function update(currentTime) {
const elapsed = currentTime - startTime;
const progress = Math.min(elapsed / duration, 1);
const easeOut = 1 - Math.pow(1 - progress, 3);
el.textContent = Math.floor(target * easeOut).toLocaleString();
if (progress < 1) requestAnimationFrame(update);
}
requestAnimationFrame(update);
}
const counterObserver = new IntersectionObserver((entries) => {
entries.forEach(entry => {
if (entry.isIntersecting) {
const target = parseFloat(entry.target.dataset.target);
if (!isNaN(target)) animateCounter(entry.target, target);
counterObserver.unobserve(entry.target);
}
});
}, { threshold: 0.5 });
document.querySelectorAll('.counter').forEach(el => counterObserver.observe(el));
// ===== DONUT CHART ANIMATION =====
function animateDonutChart() {
const segments = document.querySelectorAll('.donut-segment');
const circumference = 2 * Math.PI * 80;
let offset = 0;
const gap = 2;
segments.forEach((seg, idx) => {
const percent = parseFloat(seg.dataset.percent);
const segLength = percent - gap;
seg.style.strokeDasharray = `0 ${circumference}`;
seg.style.strokeDashoffset = `${-offset * (circumference / 100)}`;
setTimeout(() => {
seg.classList.add('animated');
seg.style.strokeDasharray = `${segLength * (circumference / 100)} ${circumference}`;
seg.style.strokeDashoffset = `${-offset * (circumference / 100)}`;
}, 100 + idx * 150);
offset += percent;
});
}
const donutObserver = new IntersectionObserver((entries) => {
entries.forEach(entry => {
if (entry.isIntersecting) {
animateDonutChart();
donutObserver.unobserve(entry.target);
}
});
}, { threshold: 0.3 });
const donutChart = document.getElementById('donutChart');
if (donutChart) {
donutObserver.observe(donutChart.closest('.glass'));
}
// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
anchor.addEventListener('click', function(e) {
const target = document.querySelector(this.getAttribute('href'));
if (target) {
e.preventDefault();
target.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
});
});
// ===== MAIN CHART (SVG-based) =====
(function() {
const chartEl = document.getElementById('mainChart');
if (!chartEl) return;
const regData = [12, 18, 15, 22, 28, 25, 32, 38, 35, 42, 48, 45, 52, 58, 55, 62, 68, 65, 72, 78, 75, 82, 88, 85, 92, 98, 95, 102, 108, 105];
const docData = [45, 52, 48, 58, 65, 62, 72, 78, 75, 85, 92, 88, 98, 105, 102, 112, 118, 115, 125, 132, 128, 138, 145, 142, 152, 158, 155, 165, 172, 168];
const maxVal = Math.max(...regData, ...docData);
function buildPath(data, w, h) {
const stepX = w / (data.length - 1);
return data.map((v, i) => {
const x = i * stepX;
const y = h - (v / maxVal) * h * 0.9 - h * 0.05;
return `${i === 0 ? 'M' : 'L'} ${x} ${y}`;
}).join(' ');
}
function buildAreaPath(data, w, h) {
const stepX = w / (data.length - 1);
const line = data.map((v, i) => {
const x = i * stepX;
const y = h - (v / maxVal) * h * 0.9 - h * 0.05;
return `${i === 0 ? 'M' : 'L'} ${x} ${y}`;
}).join(' ');
return `${line} L ${w} ${h} L 0 ${h} Z`;
}
const w = 1000, h = 300;
const regPath = buildPath(regData, w, h);
const docPath = buildPath(docData, w, h);
const regArea = buildAreaPath(regData, w, h);
const docArea = buildAreaPath(docData, w, h);
let gridLines = '';
for (let i = 0; i <= 4; i++) {
const y = (h / 4) * i;
gridLines += `<line x1="0" y1="${y}" x2="${w}" y2="${y}" stroke="rgba(255,255,255,0.04)" stroke-width="1"/>`;
}
chartEl.innerHTML = `
<svg viewBox="0 0 ${w} ${h}" class="w-full h-full" preserveAspectRatio="none">
<defs>
<linearGradient id="regGrad" x1="0" y1="0" x2="0" y2="1">
<stop offset="0%" stop-color="#3b82f6" stop-opacity="0.4"/>
<stop offset="100%" stop-color="#3b82f6" stop-opacity="0"/>
</linearGradient>
<linearGradient id="docGrad2" x1="0" y1="0" x2="0" y2="1">
<stop offset="0%" stop-color="#8b5cf6" stop-opacity="0.3"/>
<stop offset="100%" stop-color="#8b5cf6" stop-opacity="0"/>
</linearGradient>
</defs>
${gridLines}
<path d="${docArea}" fill="url(#docGrad2)"/>
<path d="${docPath}" stroke="#8b5cf6" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
<path d="${regArea}" fill="url(#regGrad)"/>
<path d="${regPath}" stroke="#3b82f6" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
`;
})();
// ===== FACE ID: dot-mesh face generation =====
(function() {
const faceSvg = document.getElementById('faceDots');
if (!faceSvg) return;
const svgNS = 'http://www.w3.org/2000/svg';
function addDot(px, py, pr, pClass) {
const dotEl = document.createElementNS(svgNS, 'circle');
dotEl.setAttribute('cx', px);
dotEl.setAttribute('cy', py);
dotEl.setAttribute('r', pr);
dotEl.setAttribute('class', pClass);
faceSvg.appendChild(dotEl);
}
const cx = 110, cy = 112, rx = 60, ry = 76;
// Inner mesh (soft dots)
for (let gx = cx - rx + 8; gx <= cx + rx - 8; gx += 13) {
for (let gy = cy - ry + 8; gy <= cy + ry - 8; gy += 13) {
const nx = (gx - cx) / rx;
const ny = (gy - cy) / ry;
if (nx * nx + ny * ny < 0.8) {
addDot(gx + (Math.random() * 4 - 2), gy + (Math.random() * 4 - 2), 1.1, 'fd fd-soft');
}
}
}
// Face contour
const contourCount = 46;
for (let i = 0; i < contourCount; i++) {
const angle = (i / contourCount) * Math.PI * 2;
addDot(cx + Math.cos(angle) * rx, cy + Math.sin(angle) * ry, 1.8, 'fd fd-main');
}
// Eyes
[[86, 100], [134, 100]].forEach(pt => {
for (let i = 0; i < 9; i++) {
const angle = Math.PI + (i / 8) * Math.PI;
addDot(pt[0] + Math.cos(angle) * 9, pt[1] + Math.sin(angle) * 4.5, 1.5, 'fd fd-main');
}
});
// Brows
[[86, 86], [134, 86]].forEach(pt => {
for (let i = 0; i < 7; i++) {
const angle = Math.PI + (i / 6) * Math.PI;
addDot(pt[0] + Math.cos(angle) * 12, pt[1] + Math.sin(angle) * 3.5, 1.3, 'fd fd-main');
}
});
// Nose
for (let i = 0; i < 6; i++) {
addDot(110 + Math.sin(i * 0.9) * 1.6, 106 + i * 6, 1.4, 'fd fd-main');
}
addDot(104, 140, 1.4, 'fd fd-main');
addDot(116, 140, 1.4, 'fd fd-main');
// Mouth (smile)
for (let i = 0; i < 9; i++) {
const mx = 92 + i * 4.5;
const my = 160 + Math.sin((i / 8) * Math.PI) * 4;
addDot(mx, my, 1.5, 'fd fd-main');
}
})();
// ===== FACE ID: 3D tilt =====
(function() {
const tiltWrap = document.getElementById('faceTiltWrap');
const tiltCard = document.getElementById('faceScanner');
if (!tiltWrap || !tiltCard) return;
tiltWrap.addEventListener('mousemove', (e) => {
if (window.innerWidth < 768) return;
const rect = tiltWrap.getBoundingClientRect();
const relX = (e.clientX - rect.left) / rect.width - 0.5;
const relY = (e.clientY - rect.top) / rect.height - 0.5;
tiltCard.style.transform = `rotateY(${relX * 14}deg) rotateX(${-relY * 12}deg)`;
});
tiltWrap.addEventListener('mouseleave', () => {
tiltCard.style.transform = 'rotateY(0deg) rotateX(0deg)';
});
})();
// ===== FACE ID: scan status cycle =====
(function() {
const scannerEl = document.getElementById('faceScanner');
const statusPill = document.getElementById('faceStatusPill');
const statusText = document.getElementById('faceStatusText');
const statusDot = document.getElementById('faceStatusDot');
if (!scannerEl || !statusPill) return;
let isVerified = false;
function renderFaceStatus() {
const tr = translations[currentLang] || translations.ru;
if (isVerified) {
scannerEl.classList.add('faceid-verified');
statusPill.classList.add('face-status-ok');
statusText.textContent = tr.faceid_status_ok;
statusDot.style.background = '#10b981';
statusDot.style.boxShadow = '0 0 10px #10b981';
} else {
scannerEl.classList.remove('faceid-verified');
statusPill.classList.remove('face-status-ok');
statusText.textContent = tr.faceid_status_scanning;
statusDot.style.background = '#3b82f6';
statusDot.style.boxShadow = '0 0 10px #3b82f6';
}
}
renderFaceStatus();
setInterval(() => {
isVerified = !isVerified;
renderFaceStatus();
}, 4200);
document.addEventListener('docsign-lang-change', renderFaceStatus);
})();

// ===== RAG DEMO: animated chat cycle =====
(function() {
const messagesEl = document.getElementById('ragMessages');
if (!messagesEl) return;

const scenarios = [
{
qKey: 'ai_demo_q1',
aKey: 'ai_demo_a1',
src: [
{ key: 'ai_demo_src1', score: '98%' },
{ key: 'ai_demo_src2', score: '94%' }
]
},
{
qKey: 'ai_demo_q2',
aKey: 'ai_demo_a2',
src: [
{ key: 'ai_demo_src1', score: '96%' }
]
}
];

let currentScenario = 0;
let pipelineStep = 0;

function getTr() {
return translations[currentLang] || translations.ru;
}

function clearMessages() {
messagesEl.innerHTML = '';
}

function addMessage(type, content, sources) {
const tr = getTr();
const msg = document.createElement('div');
msg.className = `rag-msg rag-msg-${type}`;

if (type === 'user') {
msg.innerHTML = `
<div class="rag-msg-bubble">${content}</div>
<div class="rag-msg-avatar">
<svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
</svg>
</div>
`;
} else {
let sourcesHtml = '';
if (sources && sources.length > 0) {
sourcesHtml = `
<div class="rag-sources">
<div class="rag-sources-title">
<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
<span>${tr.ai_demo_src_title}</span>
</div>
${sources.map(s => `
<div class="rag-source-item">
<div class="rag-source-icon">
<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
</div>
<span class="rag-source-name">${tr[s.key]}</span>
<span class="rag-source-score">${s.score}</span>
</div>
`).join('')}
</div>
`;
}
msg.innerHTML = `
<div class="rag-msg-avatar">
<svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
</svg>
</div>
<div class="rag-msg-bubble">
<div>${content}</div>
${sourcesHtml}
</div>
`;
}
messagesEl.appendChild(msg);
return msg;
}

function addTypingIndicator() {
const msg = document.createElement('div');
msg.className = 'rag-msg rag-msg-ai';
msg.id = 'ragTyping';
msg.innerHTML = `
<div class="rag-msg-avatar">
<svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
</svg>
</div>
<div class="rag-msg-bubble">
<div class="rag-typing"><span></span><span></span><span></span></div>
</div>
`;
messagesEl.appendChild(msg);
return msg;
}

function setActivePipelineStep(step) {
document.querySelectorAll('.rag-step').forEach(el => {
const s = parseInt(el.dataset.step);
if (s <= step) el.classList.add('active');
else el.classList.remove('active');
});
document.querySelectorAll('.rag-step-arrow').forEach((el, i) => {
if (i < step - 1) el.classList.add('active');
else el.classList.remove('active');
});
}

async function runScenario() {
const tr = getTr();
const scenario = scenarios[currentScenario];

clearMessages();
setActivePipelineStep(0);

// Step 1: user query
await sleep(300);
setActivePipelineStep(1);
addMessage('user', tr[scenario.qKey]);

// Step 2: retrieve
await sleep(1200);
setActivePipelineStep(2);

// Step 3: generate (typing)
await sleep(900);
setActivePipelineStep(3);
const typing = addTypingIndicator();

// Step 4: answer
await sleep(1400);
typing.remove();
addMessage('ai', tr[scenario.aKey], scenario.src);

// Wait before next scenario
await sleep(5000);
currentScenario = (currentScenario + 1) % scenarios.length;
runScenario();
}

function sleep(ms) {
return new Promise(resolve => setTimeout(resolve, ms));
}

// Start demo when section is visible
const aiSection = document.getElementById('ai');
let demoStarted = false;
const aiObserver = new IntersectionObserver((entries) => {
entries.forEach(entry => {
if (entry.isIntersecting && !demoStarted) {
demoStarted = true;
runScenario();
}
});
}, { threshold: 0.3 });
if (aiSection) aiObserver.observe(aiSection);

// Restart demo on language change
document.addEventListener('docsign-lang-change', () => {
if (demoStarted) {
currentScenario = 0;
runScenario();
}
});
})();
</script>
</body>
</html>