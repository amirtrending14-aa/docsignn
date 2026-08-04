@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .mode-icon { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: rgba(79, 140, 255, 0.1); border-radius: 8px; margin-bottom: 8px; }
    .mode-icon i { font-size: 20px; color: #4f8cff; }

    .doc-create-page { color: #e7ecf3; padding: 24px 16px; }
    .form-card { background: linear-gradient(180deg, rgba(22, 26, 38, 0.95), rgba(16, 19, 28, 0.95)); border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 28px; position: relative; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.4); }
    .form-card::before { content: ""; position: absolute; inset: -1px; border-radius: 16px; padding: 1px; background: linear-gradient(135deg, rgba(79,140,255,0.5), transparent 40%, transparent 60%, rgba(79,140,255,0.3)); -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0); -webkit-mask-composite: xor; mask-composite: exclude; pointer-events: none; opacity: 0.7; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #8892a6; text-decoration: none; font-size: 12px; font-weight: 600; transition: all 0.25s ease; margin-bottom: 16px; }
    .back-btn:hover { color: #fff; border-color: rgba(79,140,255, 0.5); background: rgba(79,140,255, 0.08); box-shadow: 0 0 12px rgba(79,140,255, 0.2); transform: translateX(-2px); }
    .page-title { font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 4px; display: flex; align-items: center; gap: 8px; }
    .page-title::before { content: ""; width: 4px; height: 18px; background: linear-gradient(180deg, #4f8cff, rgba(79,140,255,0.3)); border-radius: 2px; box-shadow: 0 0 8px rgba(79,140,255,0.6); }
    .page-subtitle { font-size: 11px; color: #8892a6; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 20px; }

    .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
    .field-row.single { grid-template-columns: 1fr; }
    .field-group { display: flex; flex-direction: column; }
    .field-label { font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #8892a6; margin-bottom: 6px; }
    .field-label .required { color: #ff6b6b; margin-left: 2px; }

    .input-field { width: 100%; height: 40px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 0 12px; color: #fff; font: 500 13px 'Inter', sans-serif; outline: none; transition: all 0.2s ease; }
    .input-field::placeholder { color: rgba(255,255,255,0.3); }
    .input-field:focus { border-color: rgba(79,140,255, 0.7); box-shadow: 0 0 0 2px rgba(79,140,255, 0.15), 0 0 12px rgba(79,140,255, 0.1); background: rgba(255,255,255,0.05); }
    textarea.input-field { min-height: 80px; padding: 10px 12px; resize: vertical; line-height: 1.5; height: auto; }
    select.input-field { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%238892a6' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 32px; cursor: pointer; }
    input[type="date"].input-field::-webkit-calendar-picker-indicator { filter: invert(0.8); cursor: pointer; }

    .receiver-section { margin-top: 16px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.06); }
    .section-title { font-size: 11px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #8892a6; margin-bottom: 10px; }

    .mode-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 12px; }
    .mode-btn { background: rgba(255,255,255,0.02); border: 1.5px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 12px; cursor: pointer; transition: all 0.2s ease; position: relative; color: #fff; text-align: left; width: 100%; }
    .mode-btn:hover { border-color: rgba(79,140,255, 0.5); background: rgba(79,140,255, 0.05); transform: translateY(-2px); box-shadow: 0 6px 16px rgba(79,140,255, 0.15); }
    .mode-btn.active { border-color: rgba(79,140,255, 1); background: rgba(79,140,255, 0.12); box-shadow: 0 0 16px rgba(79,140,255, 0.3), inset 0 0 8px rgba(79,140,255, 0.05); }
    .mode-btn[disabled] { opacity: 0.4; cursor: not-allowed; pointer-events: none; border-color: rgba(255, 107, 107, 0.3); }
    .mode-btn[disabled] .mode-icon { background: rgba(255, 107, 107, 0.1); border-color: rgba(255, 107, 107, 0.3); }
    .mode-btn[disabled] .mode-icon i { color: #ff6b6b; }
    .mode-btn .mode-icon { width: 28px; height: 28px; border-radius: 7px; background: rgba(79,140,255, 0.15); border: 1px solid rgba(79,140,255, 0.3); display: flex; align-items: center; justify-content: center; color: #4f8cff; font-size: 13px; margin-bottom: 8px; transition: all 0.2s ease; }
    .mode-btn.active .mode-icon { background: rgba(79,140,255, 0.3); box-shadow: 0 0 10px rgba(79,140,255, 0.4); }
    .mode-btn .mode-title { font-size: 11px; font-weight: 700; color: #fff; margin-bottom: 2px; }
    .mode-btn .mode-desc { font-size: 9px; color: #8892a6; line-height: 1.3; }
    .mode-btn .mode-check { position: absolute; top: 8px; right: 8px; width: 16px; height: 16px; border-radius: 50%; border: 1.5px solid rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; }
    .mode-btn.active .mode-check { background: #4f8cff; border-color: #4f8cff; color: #0a0d14; box-shadow: 0 0 8px rgba(79,140,255, 0.8); }
    .mode-btn.active .mode-check::after { content: "\F26A"; font-family: "bootstrap-icons"; font-size: 9px; font-weight: 900; }

    .mode-btn-region .mode-icon { background: rgba(168,85,247,0.15); border-color: rgba(168,85,247,0.35); color: #a855f7; }
    .mode-btn-region:hover { border-color: rgba(168,85,247,0.5); background: rgba(168,85,247,0.05); box-shadow: 0 6px 16px rgba(168,85,247,0.15); }
    .mode-btn-region.active { border-color: rgba(168,85,247,1); background: rgba(168,85,247,0.12); box-shadow: 0 0 16px rgba(168,85,247,0.3), inset 0 0 8px rgba(168,85,247,0.05); }
    .mode-btn-region.active .mode-icon { background: rgba(168,85,247,0.3); box-shadow: 0 0 10px rgba(168,85,247,0.4); }
    .mode-btn-region.active .mode-check { background: #a855f7; border-color: #a855f7; color: #0a0d14; box-shadow: 0 0 8px rgba(168,85,247,0.8); }

    .mode-btn-department .mode-icon { background: rgba(251, 191, 36, 0.15); border-color: rgba(251, 191, 36, 0.35); color: #fbbf24; }
    .mode-btn-department:hover { border-color: rgba(251, 191, 36, 0.5); background: rgba(251, 191, 36, 0.05); box-shadow: 0 6px 16px rgba(251, 191, 36, 0.15); }
    .mode-btn-department.active { border-color: rgba(251, 191, 36, 1); background: rgba(251, 191, 36, 0.12); box-shadow: 0 0 16px rgba(251, 191, 36, 0.3), inset 0 0 8px rgba(251, 191, 36, 0.05); }
    .mode-btn-department.active .mode-icon { background: rgba(251, 191, 36, 0.3); box-shadow: 0 0 10px rgba(251, 191, 36, 0.4); }
    .mode-btn-department.active .mode-check { background: #fbbf24; border-color: #fbbf24; color: #0a0d14; box-shadow: 0 0 8px rgba(251, 191, 36, 0.8); }

    .mode-btn-company .mode-icon { background: rgba(6, 182, 212, 0.15); border-color: rgba(6, 182, 212, 0.35); color: #06b6d4; }
    .mode-btn-company:hover { border-color: rgba(6, 182, 212, 0.5); background: rgba(6, 182, 212, 0.05); box-shadow: 0 6px 16px rgba(6, 182, 212, 0.15); }
    .mode-btn-company.active { border-color: rgba(6, 182, 212, 1); background: rgba(6, 182, 212, 0.12); box-shadow: 0 0 16px rgba(6, 182, 212, 0.3), inset 0 0 8px rgba(6, 182, 212, 0.05); }
    .mode-btn-company.active .mode-icon { background: rgba(6, 182, 212, 0.3); box-shadow: 0 0 10px rgba(6, 182, 212, 0.4); }
    .mode-btn-company.active .mode-check { background: #06b6d4; border-color: #06b6d4; color: #0a0d14; box-shadow: 0 0 8px rgba(6, 182, 212, 0.8); }

    .receiver-block { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 14px; margin-top: 10px; }
    .receiver-block.hidden { display: none; }

    .chip { display: inline-flex; align-items: center; gap: 6px; background: rgba(79,140,255, 0.15); border: 1px solid rgba(79,140,255, 0.4); color: #4f8cff; padding: 4px 10px; border-radius: 14px; font-size: 11px; font-weight: 600; }
    .chip button { background: none; border: none; color: inherit; cursor: pointer; opacity: 0.7; display: flex; padding: 0; font-size: 10px; }
    .chip button:hover { opacity: 1; color: #ff7a7a; }

    .search-dropdown { background: rgba(16, 19, 28, 0.98); border: 1px solid rgba(79,140,255,0.3); border-radius: 8px; margin-top: 6px; max-height: 200px; overflow-y: auto; box-shadow: 0 8px 24px rgba(0,0,0,0.6), 0 0 16px rgba(79,140,255,0.1); z-index: 100; position: absolute; left: 0; right: 0; width: 100%; }
    .search-dropdown.hidden { display: none !important; }
    .dropdown-item { padding: 10px 14px; border-bottom: 1px solid rgba(255,255,255,0.05); cursor: pointer; transition: all 0.15s ease; display: flex; justify-content: space-between; align-items: center; }
    .dropdown-item:last-child { border-bottom: none; }
    .dropdown-item:hover { background: rgba(79,140,255, 0.15); }
    .dropdown-item .name { font-size: 12px; font-weight: 600; color: #fff; display: block; margin-bottom: 2px; }
    .dropdown-item .meta { font-size: 10px; color: #8892a6; display: flex; flex-direction: column; gap: 2px; }
    .dropdown-item .meta span { display: block; }
    .dropdown-item .meta .company { color: #4f8cff; font-weight: 500; }
    .dropdown-item .add-icon { color: #4f8cff; font-size: 14px; opacity: 0.7; transition: all 0.2s; }
    .dropdown-item:hover .add-icon { opacity: 1; transform: scale(1.2); }
    .dropdown-empty { padding: 12px 14px; font-size: 11px; color: #8892a6; text-align: center; }
    .search-wrapper { position: relative; }

    .file-upload { display: flex; align-items: center; justify-content: space-between; height: 40px; background: rgba(255,255,255,0.03); border: 1px dashed rgba(255,255,255,0.15); border-radius: 8px; padding: 0 14px; cursor: pointer; transition: all 0.2s ease; color: #8892a6; font-size: 12px; }
    .file-upload:hover { border-color: rgba(79,140,255, 0.5); background: rgba(79,140,255, 0.05); color: #fff; }
    .file-upload input[type="file"] { display: none; }

    .existing-file { display: flex; align-items: center; justify-content: space-between; height: 40px; background: rgba(34,197,94,0.08); border: 1px solid rgba(34,197,94,0.35); border-radius: 8px; padding: 0 14px; color: #fff; font-size: 12px; margin-bottom: 8px; }
    .existing-file .file-info { display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0; }
    .existing-file .file-info i { color: #22c55e; font-size: 16px; flex-shrink: 0; }
    .existing-file .file-name { color: #fff; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .existing-file .file-actions { display: flex; gap: 6px; flex-shrink: 0; }
    .existing-file .file-action-btn { width: 28px; height: 28px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.04); color: #8892a6; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s ease; text-decoration: none; font-size: 12px; }
    .existing-file .file-action-btn:hover { color: #fff; border-color: rgba(79,140,255,0.5); background: rgba(79,140,255,0.1); }
    .existing-file .file-action-btn.danger:hover { color: #ff6b6b; border-color: rgba(255,107,107,0.5); background: rgba(255,107,107,0.1); }

    .btn-submit { appearance: none; border: 1.5px solid rgba(79,140,255, 0.6); background: linear-gradient(180deg, rgba(79,140,255, 0.2), rgba(79,140,255, 0.1)); color: #fff; font: 700 12px 'Inter', sans-serif; letter-spacing: 1px; text-transform: uppercase; padding: 12px 24px; border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 0 16px rgba(79,140,255, 0.2); transition: all 0.2s ease; width: 100%; max-width: 280px; margin: 0 auto; }
    .btn-submit:hover { background: linear-gradient(180deg, rgba(79,140,255, 0.3), rgba(79,140,255, 0.15)); border-color: rgba(79,140,255, 0.8); box-shadow: 0 0 24px rgba(79,140,255, 0.35); transform: translateY(-1px); }

    .btn-delete { appearance: none; border: 1.5px solid rgba(255,107,107, 0.6); background: linear-gradient(180deg, rgba(255,107,107, 0.15), rgba(255,107,107, 0.08)); color: #ff9999; font: 700 12px 'Inter', sans-serif; letter-spacing: 1px; text-transform: uppercase; padding: 12px 24px; border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 0 16px rgba(255,107,107, 0.15); transition: all 0.2s ease; width: 100%; max-width: 220px; }
    .btn-delete:hover { background: linear-gradient(180deg, rgba(255,107,107, 0.25), rgba(255,107,107, 0.15)); border-color: rgba(255,107,107, 0.8); color: #fff; box-shadow: 0 0 24px rgba(255,107,107, 0.35); transform: translateY(-1px); }

    .error-box { background: rgba(255, 99, 99, 0.05); border: 1px solid rgba(255, 99, 99, 0.25); border-left: 3px solid #ff6b6b; border-radius: 8px; padding: 12px; color: #ff9999; margin-bottom: 16px; }
    .error-box .title { font-weight: 700; font-size: 12px; margin-bottom: 4px; color: #ff6b6b; }
    .error-box ul { font-size: 11px; margin: 0; padding-left: 16px; }

    .byregion-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px; }
    .byregion-title { font-size: 11px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 6px; }
    .byregion-title i.region-icon { color: #a855f7; font-size: 13px; }
    .byregion-title i.dept-icon { color: #fbbf24; font-size: 13px; }
    .byregion-title i.company-icon { color: #06b6d4; font-size: 13px; }
    
    .byregion-badge { flex-shrink: 0; min-width: 24px; height: 20px; padding: 0 7px; display: inline-flex; align-items: center; justify-content: center; background: rgba(168,85,247,0.18); border: 1px solid rgba(168,85,247,0.45); color: #a855f7; border-radius: 10px; font-size: 10px; font-weight: 800; }
    .dept-badge { background: rgba(251,191,36,0.18); border-color: rgba(251,191,36,0.45); color: #fbbf24; }
    .company-badge { background: rgba(6,182,212,0.18); border-color: rgba(6,182,212,0.45); color: #06b6d4; }

    .byregion-empty { text-align: center; padding: 18px 14px; border: 1px dashed rgba(168,85,247,0.3); border-radius: 10px; color: #8892a6; font-size: 11px; line-height: 1.5; }
    .dept-empty { border-color: rgba(251,191,36,0.3); }
    .company-empty { border-color: rgba(6,182,212,0.3); }
    .byregion-empty.hidden { display: none !important; }

    .byregion-add-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; margin-top: 12px; padding: 11px 18px; border-radius: 10px; border: 1.5px solid rgba(168,85,247,0.6); background: linear-gradient(180deg, rgba(168,85,247,0.2), rgba(168,85,247,0.1)); color: #fff; font: 700 12px 'Inter', sans-serif; text-decoration: none; transition: all 0.2s ease; width: 100%; }
    .byregion-add-btn:hover { background: linear-gradient(180deg, rgba(168,85,247,0.3), rgba(168,85,247,0.15)); border-color: rgba(168,85,247,0.8); box-shadow: 0 0 20px rgba(168,85,247,0.3); transform: translateY(-1px); color: #fff; text-decoration: none; }
    
    .dept-add-btn { border-color: rgba(251,191,36,0.6); background: linear-gradient(180deg, rgba(251,191,36,0.2), rgba(251,191,36,0.1)); }
    .dept-add-btn:hover { background: linear-gradient(180deg, rgba(251,191,36,0.3), rgba(251,191,36,0.15)); border-color: rgba(251,191,36,0.8); box-shadow: 0 0 20px rgba(251,191,36,0.3); }
    
    .company-add-btn { border-color: rgba(6,182,212,0.6); background: linear-gradient(180deg, rgba(6,182,212,0.2), rgba(6,182,212,0.1)); }
    .company-add-btn:hover { background: linear-gradient(180deg, rgba(6,182,212,0.3), rgba(6,182,212,0.15)); border-color: rgba(6,182,212,0.8); box-shadow: 0 0 20px rgba(6,182,212,0.3); }

    .byregion-add-btn i { font-size: 14px; }
    .byregion-error { font-size: 10px; color: #ff6b6b; margin-top: 8px; font-weight: 600; display: none; }

    .carried-list { display: flex; flex-direction: column; gap: 8px; }
    .carried-list.hidden { display: none !important; }
    .carried-item { display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 11px; padding: 10px 12px; transition: all 0.2s ease; }
    .carried-item:hover { border-color: rgba(168,85,247,0.35); background: rgba(168,85,247,0.05); }
    
    .dept-item:hover { border-color: rgba(251,191,36,0.35); background: rgba(251,191,36,0.05); }
    .dept-item .carried-avatar { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #0a0d14; box-shadow: 0 4px 12px rgba(251,191,36,0.35); }
    .dept-item .carried-meta i { color: #fbbf24; }

    .company-item:hover { border-color: rgba(6,182,212,0.35); background: rgba(6,182,212,0.05); }
    .company-item .carried-avatar { background: linear-gradient(135deg, #06b6d4, #0891b2); color: #0a0d14; box-shadow: 0 4px 12px rgba(6,182,212,0.35); }
    .company-item .carried-meta i { color: #06b6d4; }

    .carried-avatar { width: 36px; height: 36px; flex-shrink: 0; border-radius: 50%; background: linear-gradient(135deg, #a855f7, #7c3aed); display: flex; align-items: center; justify-content: center; color: #1a0625; font-size: 14px; font-weight: 800; box-shadow: 0 4px 12px rgba(168,85,247,0.35); text-transform: uppercase; }
    .carried-info { flex: 1; min-width: 0; }
    .carried-name { font-size: 12px; font-weight: 700; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .carried-meta { display: flex; flex-wrap: wrap; gap: 4px 12px; margin-top: 3px; }
    .carried-meta span { display: inline-flex; align-items: center; gap: 4px; font-size: 10px; color: #8892a6; }
    .carried-meta i { font-size: 10px; color: #a855f7; }
    .carried-remove { flex-shrink: 0; width: 28px; height: 28px; border-radius: 8px; background: rgba(255,107,107,0.08); border: 1px solid rgba(255,107,107,0.25); color: #ff6b6b; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 12px; transition: all 0.2s ease; }
    .carried-remove:hover { background: rgba(255,107,107,0.2); border-color: rgba(255,107,107,0.6); color: #fff; transform: scale(1.08); }

    .ai-generator-card { background: linear-gradient(135deg, rgba(79,140,255,0.08), rgba(79,140,255,0.02)); border: 1px solid rgba(79,140,255,0.3); border-radius: 16px; padding: 20px; position: relative; overflow: hidden; margin-bottom: 24px; }
    .ai-generator-card::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, transparent, #4f8cff, transparent); animation: shimmer 3s infinite; }
    @keyframes shimmer { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
    .ai-header { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; }
    .ai-icon { width: 44px; height: 44px; background: linear-gradient(135deg, #4f8cff, #6366f1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 20px; box-shadow: 0 4px 16px rgba(79,140,255,0.4); }
    .ai-title { font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 2px; }
    .ai-subtitle { font-size: 11px; color: #8892a6; }
    .ai-input-group { margin-bottom: 14px; }
    .ai-textarea { min-height: 90px; resize: vertical; font-size: 13px; line-height: 1.5; width: 100%; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 10px 12px; color: #fff; font: 500 13px 'Inter', sans-serif; outline: none; transition: all 0.2s ease; }
    .ai-textarea::placeholder { color: rgba(255,255,255,0.3); }
    .ai-textarea:focus { border-color: rgba(79,140,255, 0.7); box-shadow: 0 0 0 2px rgba(79,140,255, 0.15), 0 0 12px rgba(79,140,255, 0.1); background: rgba(255,255,255,0.05); }
    .ai-format-selector { display: flex; gap: 10px; margin-top: 10px; }
    .format-option { flex: 1; cursor: pointer; }
    .format-option input[type="radio"] { display: none; }
    .format-label { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #8892a6; font-size: 12px; font-weight: 600; transition: all 0.2s ease; }
    .format-option input[type="radio"]:checked + .format-label { background: rgba(79,140,255,0.15); border-color: rgba(79,140,255,0.5); color: #4f8cff; box-shadow: 0 0 12px rgba(79,140,255,0.2); }
    .format-label i { font-size: 16px; }
    .ai-generate-btn { width: 100%; padding: 12px; background: linear-gradient(135deg, #4f8cff, #6366f1); border: none; border-radius: 10px; color: #fff; font: 700 13px 'Inter', sans-serif; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 16px rgba(79,140,255,0.4); transition: all 0.3s ease; }
    .ai-generate-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(79,140,255,0.5); }
    .ai-generate-btn:disabled { opacity: 0.6; cursor: not-allowed; }
    .ai-generate-btn i { font-size: 16px; }
    .ai-status { margin-top: 14px; padding: 14px; background: rgba(79,140,255,0.08); border: 1px solid rgba(79,140,255,0.3); border-radius: 10px; display: flex; align-items: center; gap: 12px; }
    .ai-status.hidden { display: none; }
    .ai-status-icon { width: 32px; height: 32px; background: rgba(79,140,255,0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #4f8cff; font-size: 16px; animation: pulse 1.5s infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
    .ai-status-text { font-size: 12px; color: #fff; font-weight: 600; }
    .ai-questions { margin-top: 14px; padding: 14px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; }
    .ai-questions.hidden { display: none; }
    .questions-title { font-size: 12px; font-weight: 700; color: #4f8cff; margin-bottom: 10px; display: flex; align-items: center; gap: 6px; }
    .question-item { margin-bottom: 10px; }
    .question-text { font-size: 11px; color: #fff; margin-bottom: 6px; font-weight: 600; }
    .question-input { width: 100%; height: 36px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; padding: 0 10px; color: #fff; font-size: 12px; outline: none; }
    .question-input:focus { border-color: rgba(79,140,255,0.5); background: rgba(255,255,255,0.05); }
    .ai-submit-btn { width: 100%; padding: 10px; background: rgba(79,140,255,0.15); border: 1px solid rgba(79,140,255,0.4); border-radius: 8px; color: #4f8cff; font: 600 12px 'Inter', sans-serif; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 10px; transition: all 0.2s ease; }
    .ai-submit-btn:hover { background: rgba(79,140,255,0.25); border-color: rgba(79,140,255,0.6); }
    .ai-result { margin-top: 14px; padding: 14px; background: rgba(34,197,94,0.08); border: 1px solid rgba(34,197,94,0.3); border-radius: 10px; }
    .ai-result.hidden { display: none; }
    .result-header { font-size: 13px; font-weight: 700; color: #22c55e; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
    .result-actions { display: flex; gap: 10px; }
    .download-btn { flex: 1; padding: 10px; background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.4); border-radius: 8px; color: #22c55e; font: 600 12px 'Inter', sans-serif; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s ease; }
    .download-btn:hover { background: rgba(34,197,94,0.25); border-color: rgba(34,197,94,0.6); }
    .ai-error { margin-top: 14px; padding: 12px; background: rgba(255,99,99,0.08); border: 1px solid rgba(255,99,99,0.3); border-radius: 8px; color: #ff9999; font-size: 12px; }
    .ai-error.hidden { display: none; }

    .action-row { display: flex; gap: 12px; justify-content: center; align-items: center; margin-top: 16px; flex-wrap: wrap; }

    @media (max-width: 992px) {
        .doc-create-page { padding: 20px 14px; }
        .form-card { padding: 24px; border-radius: 14px; }
        .ai-generator-card { padding: 18px; border-radius: 14px; margin-bottom: 20px; }
        .mode-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
        .mode-btn { padding: 10px; }
        .mode-btn .mode-icon { width: 26px; height: 26px; font-size: 12px; }
        .mode-btn .mode-title { font-size: 10px; }
        .mode-btn .mode-desc { font-size: 8px; }
        .ai-title { font-size: 15px; }
        .ai-icon { width: 40px; height: 40px; font-size: 18px; border-radius: 10px; }
    }
    @media (max-width: 768px) {
        .doc-create-page { padding: 18px 12px; }
        .form-card { padding: 20px; border-radius: 14px; }
        .ai-generator-card { padding: 16px; border-radius: 14px; margin-bottom: 18px; }
        .back-btn { padding: 7px 12px; font-size: 11px; margin-bottom: 14px; }
        .page-title { font-size: 16px; gap: 7px; }
        .page-title::before { width: 3px; height: 16px; }
        .page-subtitle { font-size: 10px; letter-spacing: 0.8px; margin-bottom: 16px; }
        .field-row { grid-template-columns: 1fr; gap: 10px; margin-bottom: 10px; }
        .field-label { font-size: 9px; letter-spacing: 1px; margin-bottom: 5px; }
        .input-field { height: 38px; font-size: 12px; padding: 0 11px; border-radius: 7px; }
        textarea.input-field { min-height: 70px; padding: 9px 11px; }
        select.input-field { padding-right: 30px; }
        .receiver-section { margin-top: 14px; padding-top: 14px; }
        .section-title { font-size: 10px; letter-spacing: 1px; margin-bottom: 8px; }
        .mode-grid { grid-template-columns: repeat(2, 1fr); gap: 6px; }
        .mode-btn { padding: 10px; border-radius: 8px; }
        .mode-btn .mode-icon { width: 26px; height: 26px; font-size: 12px; margin-bottom: 6px; }
        .mode-btn .mode-title { font-size: 10px; }
        .mode-btn .mode-desc { font-size: 8px; }
        .mode-btn .mode-check { width: 14px; height: 14px; top: 6px; right: 6px; }
        .receiver-block { padding: 12px; border-radius: 8px; margin-top: 8px; }
        .chip { font-size: 10px; padding: 3px 8px; border-radius: 12px; }
        .file-upload, .existing-file { height: 38px; font-size: 11px; padding: 0 12px; border-radius: 7px; }
        .btn-submit { padding: 11px 20px; font-size: 11px; border-radius: 8px; max-width: 260px; }
        .btn-delete { padding: 11px 20px; font-size: 11px; border-radius: 8px; max-width: 220px; }
        .error-box { padding: 10px; border-radius: 7px; margin-bottom: 14px; }
        .error-box .title { font-size: 11px; }
        .error-box ul { font-size: 10px; }
        .ai-header { gap: 12px; margin-bottom: 14px; }
        .ai-icon { width: 38px; height: 38px; font-size: 17px; border-radius: 10px; }
        .ai-title { font-size: 14px; }
        .ai-subtitle { font-size: 10px; }
        .ai-textarea { min-height: 80px; font-size: 12px; padding: 9px 11px; border-radius: 7px; }
        .ai-format-selector { gap: 8px; margin-top: 8px; }
        .format-label { padding: 9px; font-size: 11px; border-radius: 7px; gap: 6px; }
        .format-label i { font-size: 14px; }
        .ai-generate-btn { padding: 11px; font-size: 12px; border-radius: 8px; }
        .ai-generate-btn i { font-size: 14px; }
        .ai-status { padding: 12px; border-radius: 8px; gap: 10px; margin-top: 12px; }
        .ai-status-icon { width: 28px; height: 28px; font-size: 14px; border-radius: 7px; }
        .ai-status-text { font-size: 11px; }
        .ai-questions { padding: 12px; border-radius: 8px; margin-top: 12px; }
        .questions-title { font-size: 11px; margin-bottom: 8px; }
        .question-text { font-size: 10px; }
        .question-input { height: 34px; font-size: 11px; border-radius: 5px; }
        .ai-submit-btn { padding: 9px; font-size: 11px; border-radius: 7px; margin-top: 8px; }
        .ai-result { padding: 12px; border-radius: 8px; margin-top: 12px; }
        .result-header { font-size: 12px; margin-bottom: 8px; }
        .download-btn { padding: 9px; font-size: 11px; border-radius: 7px; }
        .ai-error { padding: 10px; font-size: 11px; border-radius: 7px; margin-top: 12px; }
        .search-dropdown { max-height: 180px; border-radius: 7px; }
        .dropdown-item { padding: 9px 12px; }
        .dropdown-item .name { font-size: 11px; }
        .dropdown-item .meta { font-size: 9px; }
        .dropdown-item .add-icon { font-size: 12px; }
        .dropdown-empty { padding: 10px 12px; font-size: 10px; }
        .carried-item { gap: 10px; padding: 9px 10px; border-radius: 10px; }
        .carried-avatar { width: 32px; height: 32px; font-size: 13px; }
        .carried-name { font-size: 11px; }
        .carried-meta span { font-size: 9px; }
        .carried-remove { width: 26px; height: 26px; font-size: 11px; }
        .byregion-add-btn { padding: 10px 16px; font-size: 11px; }
    }
    @media (max-width: 576px) {
        .doc-create-page { padding: 16px 10px; }
        .form-card { padding: 18px; border-radius: 12px; }
        .ai-generator-card { padding: 14px; border-radius: 12px; margin-bottom: 16px; }
        .back-btn { padding: 6px 11px; font-size: 10px; margin-bottom: 12px; border-radius: 7px; }
        .page-title { font-size: 15px; gap: 6px; }
        .page-title::before { width: 3px; height: 15px; }
        .page-subtitle { font-size: 9px; letter-spacing: 0.7px; margin-bottom: 14px; }
        .field-row { gap: 8px; margin-bottom: 8px; }
        .field-label { font-size: 9px; letter-spacing: 0.9px; margin-bottom: 4px; }
        .input-field { height: 36px; font-size: 12px; padding: 0 10px; border-radius: 6px; }
        textarea.input-field { min-height: 65px; padding: 8px 10px; }
        .receiver-section { margin-top: 12px; padding-top: 12px; }
        .section-title { font-size: 9px; letter-spacing: 0.9px; margin-bottom: 7px; }
        .mode-grid { grid-template-columns: 1fr; gap: 6px; }
        .mode-btn { padding: 10px 12px; border-radius: 8px; flex-direction: row; align-items: center; gap: 10px; display: flex; }
        .mode-btn .mode-icon { width: 28px; height: 28px; font-size: 13px; margin-bottom: 0; flex-shrink: 0; }
        .mode-btn .mode-title { font-size: 11px; margin-bottom: 1px; }
        .mode-btn .mode-desc { font-size: 9px; }
        .mode-btn .mode-check { width: 15px; height: 15px; top: 50%; right: 10px; transform: translateY(-50%); }
        .receiver-block { padding: 11px; border-radius: 7px; margin-top: 7px; }
        .chip { font-size: 10px; padding: 3px 7px; border-radius: 11px; gap: 5px; }
        .file-upload, .existing-file { height: 36px; font-size: 11px; padding: 0 11px; border-radius: 6px; }
        .btn-submit { padding: 10px 18px; font-size: 10px; border-radius: 7px; max-width: 240px; letter-spacing: 0.8px; }
        .btn-delete { padding: 10px 18px; font-size: 10px; border-radius: 7px; max-width: 200px; letter-spacing: 0.8px; }
        .error-box { padding: 9px; border-radius: 6px; margin-bottom: 12px; }
        .error-box .title { font-size: 10px; }
        .error-box ul { font-size: 9px; }
        .ai-header { gap: 10px; margin-bottom: 12px; }
        .ai-icon { width: 36px; height: 36px; font-size: 16px; border-radius: 9px; }
        .ai-title { font-size: 13px; }
        .ai-subtitle { font-size: 9px; }
        .ai-textarea { min-height: 75px; font-size: 11px; padding: 8px 10px; border-radius: 6px; }
        .ai-format-selector { gap: 6px; margin-top: 7px; }
        .format-label { padding: 8px; font-size: 10px; border-radius: 6px; gap: 5px; }
        .format-label i { font-size: 13px; }
        .ai-generate-btn { padding: 10px; font-size: 11px; border-radius: 7px; }
        .ai-generate-btn i { font-size: 13px; }
        .ai-status { padding: 10px; border-radius: 7px; gap: 9px; margin-top: 10px; }
        .ai-status-icon { width: 26px; height: 26px; font-size: 13px; border-radius: 6px; }
        .ai-status-text { font-size: 10px; }
        .ai-questions { padding: 10px; border-radius: 7px; margin-top: 10px; }
        .questions-title { font-size: 10px; margin-bottom: 7px; }
        .question-item { margin-bottom: 8px; }
        .question-text { font-size: 10px; margin-bottom: 5px; }
        .question-input { height: 32px; font-size: 10px; border-radius: 5px; padding: 0 9px; }
        .ai-submit-btn { padding: 8px; font-size: 10px; border-radius: 6px; margin-top: 7px; }
        .ai-result { padding: 10px; border-radius: 7px; margin-top: 10px; }
        .result-header { font-size: 11px; margin-bottom: 7px; }
        .download-btn { padding: 8px; font-size: 10px; border-radius: 6px; }
        .ai-error { padding: 9px; font-size: 10px; border-radius: 6px; margin-top: 10px; }
        .search-dropdown { max-height: 160px; border-radius: 6px; }
        .dropdown-item { padding: 8px 10px; }
        .dropdown-item .name { font-size: 10px; }
        .dropdown-item .meta { font-size: 9px; }
        .dropdown-item .add-icon { font-size: 11px; }
        .dropdown-empty { padding: 9px 10px; font-size: 9px; }
        .carried-list { gap: 7px; }
        .carried-item { gap: 9px; padding: 8px 9px; border-radius: 9px; }
        .carried-avatar { width: 30px; height: 30px; font-size: 12px; }
        .carried-name { font-size: 11px; }
        .carried-meta { gap: 3px 10px; margin-top: 2px; }
        .carried-meta span { font-size: 9px; }
        .carried-remove { width: 25px; height: 25px; font-size: 11px; border-radius: 7px; }
        .byregion-add-btn { padding: 9px 14px; font-size: 10px; }
    }
    @media (max-width: 480px) {
        .doc-create-page { padding: 14px 8px; }
        .form-card { padding: 16px; border-radius: 10px; }
        .ai-generator-card { padding: 12px; border-radius: 10px; margin-bottom: 14px; }
        .back-btn { padding: 5px 10px; font-size: 10px; margin-bottom: 10px; border-radius: 6px; gap: 6px; }
        .page-title { font-size: 14px; gap: 5px; }
        .page-title::before { width: 3px; height: 14px; }
        .page-subtitle { font-size: 9px; letter-spacing: 0.6px; margin-bottom: 12px; }
        .field-row { gap: 7px; margin-bottom: 7px; }
        .field-label { font-size: 8px; letter-spacing: 0.8px; margin-bottom: 4px; }
        .input-field { height: 34px; font-size: 11px; padding: 0 9px; border-radius: 6px; }
        textarea.input-field { min-height: 60px; padding: 7px 9px; font-size: 11px; }
        .receiver-section { margin-top: 10px; padding-top: 10px; }
        .section-title { font-size: 9px; letter-spacing: 0.8px; margin-bottom: 6px; }
        .mode-grid { gap: 5px; margin-bottom: 10px; }
        .mode-btn { padding: 9px 11px; border-radius: 7px; gap: 9px; }
        .mode-btn .mode-icon { width: 26px; height: 26px; font-size: 12px; border-radius: 6px; }
        .mode-btn .mode-title { font-size: 10px; }
        .mode-btn .mode-desc { font-size: 8px; }
        .mode-btn .mode-check { width: 14px; height: 14px; right: 9px; }
        .receiver-block { padding: 10px; border-radius: 6px; margin-top: 6px; }
        .chip { font-size: 9px; padding: 2px 6px; border-radius: 10px; gap: 4px; }
        .chip button { font-size: 9px; }
        .file-upload, .existing-file { height: 34px; font-size: 10px; padding: 0 10px; border-radius: 6px; }
        .btn-submit { padding: 9px 16px; font-size: 10px; border-radius: 6px; max-width: 220px; letter-spacing: 0.7px; gap: 6px; }
        .btn-delete { padding: 9px 14px; font-size: 10px; border-radius: 6px; max-width: 180px; letter-spacing: 0.7px; gap: 6px; }
        .btn-submit i, .btn-delete i { font-size: 12px; }
        .error-box { padding: 8px; border-radius: 6px; margin-bottom: 10px; border-left-width: 2px; }
        .error-box .title { font-size: 10px; margin-bottom: 3px; }
        .error-box ul { font-size: 9px; padding-left: 14px; }
        .ai-header { gap: 9px; margin-bottom: 10px; }
        .ai-icon { width: 32px; height: 32px; font-size: 15px; border-radius: 8px; }
        .ai-title { font-size: 12px; }
        .ai-subtitle { font-size: 9px; }
        .ai-textarea { min-height: 70px; font-size: 11px; padding: 7px 9px; border-radius: 6px; }
        .ai-format-selector { gap: 5px; margin-top: 6px; }
        .format-label { padding: 7px; font-size: 10px; border-radius: 6px; gap: 4px; }
        .format-label i { font-size: 12px; }
        .ai-generate-btn { padding: 9px; font-size: 10px; border-radius: 6px; gap: 6px; }
        .ai-generate-btn i { font-size: 12px; }
        .ai-status { padding: 9px; border-radius: 6px; gap: 8px; margin-top: 9px; }
        .ai-status-icon { width: 24px; height: 24px; font-size: 12px; border-radius: 5px; }
        .ai-status-text { font-size: 10px; }
        .ai-questions { padding: 9px; border-radius: 6px; margin-top: 9px; }
        .questions-title { font-size: 10px; margin-bottom: 6px; gap: 5px; }
        .question-item { margin-bottom: 7px; }
        .question-text { font-size: 9px; margin-bottom: 4px; }
        .question-input { height: 30px; font-size: 10px; border-radius: 5px; padding: 0 8px; }
        .ai-submit-btn { padding: 7px; font-size: 10px; border-radius: 5px; margin-top: 6px; gap: 5px; }
        .ai-result { padding: 9px; border-radius: 6px; margin-top: 9px; }
        .result-header { font-size: 10px; margin-bottom: 6px; gap: 6px; }
        .result-actions { gap: 8px; }
        .download-btn { padding: 7px; font-size: 10px; border-radius: 5px; gap: 5px; }
        .ai-error { padding: 8px; font-size: 9px; border-radius: 5px; margin-top: 9px; }
        .search-dropdown { max-height: 150px; border-radius: 5px; margin-top: 5px; }
        .dropdown-item { padding: 7px 9px; }
        .dropdown-item .name { font-size: 10px; margin-bottom: 1px; }
        .dropdown-item .meta { font-size: 8px; }
        .dropdown-item .add-icon { font-size: 10px; }
        .dropdown-empty { padding: 8px 9px; font-size: 9px; }
        .carried-item { gap: 8px; padding: 7px 8px; border-radius: 8px; }
        .carried-avatar { width: 28px; height: 28px; font-size: 11px; }
        .carried-name { font-size: 10px; }
        .carried-meta span { font-size: 8px; gap: 3px; }
        .carried-meta i { font-size: 9px; }
        .carried-remove { width: 24px; height: 24px; font-size: 10px; border-radius: 6px; }
        .byregion-empty { padding: 14px 10px; font-size: 10px; }
        .byregion-add-btn { padding: 8px 12px; font-size: 10px; }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    }
</style>

<div class="doc-create-page">
    <div class="max-w-3xl mx-auto">

        <a href="{{ route('documents.index') }}" class="back-btn">
            <i class="bi bi-arrow-left"></i>
            <span data-i18n="back">Назад</span>
        </a>

        @if($errors->any())
        <div class="error-box">
            <div class="title" data-i18n="errorTitle">Ошибка при обновлении документа</div>
            <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="ai-generator-card">
            <div class="ai-header">
                <div class="ai-icon"><i class="bi bi-stars"></i></div>
                <div>
                    <div class="ai-title" data-i18n="aiTitle">ИИ Помощник редактирования</div>
                    <div class="ai-subtitle" data-i18n="aiSubtitle">Опиши изменения — ИИ поможет обновить документ</div>
                </div>
            </div>
            <div class="ai-input-group">
                <textarea id="aiPrompt" class="ai-textarea" placeholder="Например: Измени сумму договора на 70000 руб/мес, добавь пункт о штрафе..." rows="3"></textarea>
                <div class="ai-format-selector">
                    <label class="format-option">
                        <input type="radio" name="ai_format" value="pdf" checked>
                        <span class="format-label"><i class="bi bi-file-earmark-pdf"></i> PDF</span>
                    </label>
                    <label class="format-option">
                        <input type="radio" name="ai_format" value="word">
                        <span class="format-label"><i class="bi bi-file-earmark-word"></i> Word</span>
                    </label>
                </div>
            </div>
            <button type="button" id="generateBtn" class="ai-generate-btn"><i class="bi bi-magic"></i> <span data-i18n="aiGenerate">Применить изменения с ИИ</span></button>
            <div id="aiStatus" class="ai-status hidden"><div class="ai-status-icon"><i class="bi bi-hourglass-split"></i></div><div class="ai-status-text" data-i18n="aiStatus">ИИ обрабатывает изменения...</div></div>
            <div id="aiQuestions" class="ai-questions hidden">
                <div class="questions-title"><i class="bi bi-question-circle"></i> <span data-i18n="aiQuestionsTitle">ИИ задаёт уточняющие вопросы:</span></div>
                <div id="questionsList"></div>
                <button type="button" id="submitAnswers" class="ai-submit-btn"><i class="bi bi-check-circle"></i> <span data-i18n="aiSubmitAnswers">Отправить ответы</span></button>
            </div>
            <div id="aiResult" class="ai-result hidden">
                <div class="result-header"><i class="bi bi-check-circle-fill"></i> <span data-i18n="aiResultTitle">Изменения применены!</span></div>
                <div class="result-actions"><a id="downloadLink" href="#" class="download-btn" download><i class="bi bi-download"></i> <span data-i18n="aiDownload">Скачать обновлённый документ</span></a></div>
            </div>
            <div id="aiError" class="ai-error hidden"></div>
        </div>

        <div class="form-card">
            <h1 class="page-title" data-i18n="pageTitle">Редактирование документа</h1>
            <p class="page-subtitle" data-i18n="pageSubtitle">Измените информацию о документе № {{ $document->number }}</p>

            <form action="{{ route('documents.update', $document->id) }}" method="POST" enctype="multipart/form-data" id="documentForm">
                @csrf
                @method('PUT')

                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label"><span data-i18n="labelNumber">Номер документа</span> <span class="required">*</span></label>
                        <input type="text" name="number" id="field-number" class="input-field" value="{{ old('number', $document->number) }}" data-i18n-placeholder="numberPlaceholder" placeholder="№ 001" required>
                    </div>
                    <div class="field-group">
                        <label class="field-label"><span data-i18n="labelType">Тип документа</span> <span class="required">*</span></label>
                        <input type="text" name="type" id="field-type" class="input-field" data-i18n-placeholder="typePlaceholder" placeholder="Договор, Акт..." value="{{ old('type', $document->type) }}" required>
                    </div>
                </div>

                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label"><span data-i18n="labelStatus">Статус документа</span> <span class="required">*</span></label>
                        <select name="status" id="field-status" class="input-field" required>
                            <option value="active" {{ old('status', $document->status) == 'active' ? 'selected' : '' }} data-i18n="statusSend">Отправить на подпись</option>
                            <option value="draft" {{ old('status', $document->status) == 'draft' ? 'selected' : '' }} data-i18n="statusDraft">Сохранить как черновик</option>
                        </select>
                    </div>
                  
                    <div class="field-group">
                        <label class="field-label" data-i18n="labelDeadline">Дедлайн</label>
                        <input type="date" name="deadline" id="field-deadline" class="input-field" value="{{ old('deadline', $document->deadline ? \Illuminate\Support\Str::before($document->deadline, ' ') : '') }}">
                    </div>
                </div>

                <div class="field-row single">
                    <div class="field-group">
                        <label class="field-label"><span data-i18n="labelTitle">Заголовок</span> <span class="required">*</span></label>
                        <input type="text" name="title" id="field-title" class="input-field" data-i18n-placeholder="titlePlaceholder" placeholder="Название документа" value="{{ old('title', $document->title) }}" required>
                    </div>
                </div>

                <div class="field-row single">
                    <div class="field-group">
                        <label class="field-label" data-i18n="labelDescription">Описание</label>
                        <textarea name="content" id="field-content" rows="3" class="input-field" data-i18n-placeholder="descriptionPlaceholder" placeholder="Краткое описание документа...">{{ old('content', $document->content) }}</textarea>
                    </div>
                </div>

                <div class="field-row single">
                    <div class="field-group">
                        <label class="field-label"><span data-i18n="labelFile">Файл документа</span></label>
                        @if($document->file_path)
                        <div class="existing-file" id="existing-file-box">
                            <div class="file-info">
                                <i class="bi bi-file-earmark-check-fill"></i>
                                <span class="file-name">{{ basename($document->file_path) }}</span>
                            </div>
                            <div class="file-actions">
                                <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="file-action-btn" title="Открыть">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ asset('storage/' . $document->file_path) }}" download class="file-action-btn" title="Скачать">
                                    <i class="bi bi-download"></i>
                                </a>
                                <button type="button" class="file-action-btn danger" id="remove-existing-file" title="Удалить файл">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="remove_existing_file" id="remove_existing_file" value="0">
                        @endif

                        <label class="file-upload" id="file-upload-box">
                            <span id="file-name" data-i18n="filePlaceholder">{{ $document->file_path ? 'Заменить файл...' : 'Выберите файл...' }}</span>
                            <i class="bi bi-paperclip"></i>
                            <input type="file" name="file_path" id="file-input">
                        </label>
                        <input type="hidden" name="temp_file_path" id="temp-file-path" value="">
                        <div id="file-upload-status" style="font-size:10px; margin-top:4px; display:none;"></div>
                    </div>
                </div>

                <div class="receiver-section">
                    <div class="section-title">
                        <span data-i18n="labelReceiverMode">Способ отправки</span>
                        <span class="required" style="color:#ff6b6b">*</span>
                    </div>

                    <div class="mode-grid">
                        <button type="button" data-mode="all_team" class="mode-btn {{ !(auth()->user()->company_id ?? false) ? 'disabled-mode' : '' }}"
                                @if(!(auth()->user()->company_id ?? false)) disabled title="Доступно только для пользователей с компанией" @endif>
                            <div class="mode-icon"><i class="bi bi-people-fill"></i></div>
                            <div class="mode-title" data-i18n="modeAllTeam">Всей команде</div>
                            <div class="mode-desc">
                                @if(auth()->user()->company_id ?? false)
                                <span data-i18n="modeAllTeamDesc">Всем участникам</span>
                                @else
                                <span style="color:#ff6b6b" data-i18n="modeAllTeamDescNoCompany">Требуется компания</span>
                                @endif
                            </div>
                            <div class="mode-check"></div>
                        </button>

                        <button type="button" data-mode="select_team" class="mode-btn">
                            <div class="mode-icon"><i class="bi bi-person-check-fill"></i></div>
                            <div class="mode-title" data-i18n="modeSelectTeam">Выбрать</div>
                            <div class="mode-desc" data-i18n="modeSelectTeamDesc">До 5 человек</div>
                            <div class="mode-check"></div>
                        </button>

                        <button type="button" data-mode="other_company" class="mode-btn">
                            <div class="mode-icon"><i class="bi bi-building"></i></div>
                            <div class="mode-title" data-i18n="modeOtherCompany">Другая команда</div>
                            <div class="mode-desc" data-i18n="modeOtherCompanyDesc">Внешний получатель</div>
                            <div class="mode-check"></div>
                        </button>

                        <button type="button" data-mode="by_region" class="mode-btn mode-btn-region">
                            <div class="mode-icon"><i class="bi bi-geo-alt-fill"></i></div>
                            <div class="mode-title" data-i18n="modeByRegion">По региону</div>
                            <div class="mode-desc" data-i18n="modeByRegionDesc">Выбор по области/городу</div>
                            <div class="mode-check"></div>
                        </button>

                        <button type="button" data-mode="by_department" class="mode-btn mode-btn-department">
                            <div class="mode-icon"><i class="bi bi-building-fill"></i></div>
                            <div class="mode-title" data-i18n="modeByDepartment">По отделам</div>
                            <div class="mode-desc" data-i18n="modeByDepartmentDesc">Выбор из отделов</div>
                            <div class="mode-check"></div>
                        </button>

                        <button type="button" data-mode="by_company" class="mode-btn mode-btn-company">
                            <div class="mode-icon"><i class="bi bi-diagram-3-fill"></i></div>
                            <div class="mode-title" data-i18n="modeByCompany">Из дерева компаний</div>
                            <div class="mode-desc" data-i18n="modeByCompanyDesc">Выбрать компанию и сотрудников</div>
                            <div class="mode-check"></div>
                        </button>
                    </div>

                    <input type="hidden" name="receiver_mode" id="receiver_mode" value="{{ old('receiver_mode', $document->receiver_mode ?? '') }}">

                    <div id="mode-all_team" class="receiver-block hidden">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <i class="bi bi-info-circle-fill" style="color:#4f8cff;font-size:14px;"></i>
                            <div>
                                <p style="font-size:11px;font-weight:600;color:#fff;" data-i18n="allTeamInfo">Отправка всем участникам</p>
                                <p style="font-size:10px;color:#8892a6;margin-top:2px;">
                                    <span data-i18n="receiversCount">Получателей:</span>
                                    <strong style="color:#4f8cff;">{{ $teamUsers->count() }}</strong>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div id="mode-select_team" class="receiver-block hidden">
                        <label class="field-label" data-i18n="selectReceiversLabel">Выберите получателей (до 5)</label>
                        <div class="search-wrapper">
                            <input type="text" id="team-search" class="input-field" data-i18n-placeholder="searchPlaceholder" placeholder="Введите имя, email или телефон..." autocomplete="off">
                            <div id="team-list" class="search-dropdown hidden"></div>
                        </div>
                        <div id="team-selected" style="display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;min-height:28px;">
                            <span style="font-size:10px;color:#8892a6;" id="team-placeholder" data-i18n="selectedPlaceholder">Выбранные пользователи появятся здесь...</span>
                        </div>
                        <input type="hidden" name="team_receivers" id="team_receivers" value="">
                        <p id="team-error" style="font-size:10px;color:#ff6b6b;margin-top:6px;font-weight:600;display:none;">
                             <span data-i18n="selectError">Выберите хотя бы одного получателя</span>
                        </p>
                    </div>

                    <div id="mode-other_company" class="receiver-block hidden">
                        <label class="field-label" data-i18n="searchCompanyLabel">Поиск получателя из другой команды</label>
                        <div class="search-wrapper">
                            <input type="text" id="company-search" class="input-field" data-i18n-placeholder="searchPlaceholder" placeholder="Введите имя, email или телефон..." autocomplete="off">
                            <div id="company-list" class="search-dropdown hidden"></div>
                        </div>
                        <div id="company-selected" style="margin-top:10px;min-height:28px;"></div>
                        <input type="hidden" name="other_receiver_id" id="company_receiver" value="">
                    </div>

                    @php
                    $carriedUsers = session('selected_recipients')
                    ? \App\Models\User::whereIn('id', session('selected_recipients'))->get(['id','name','email','phone'])
                    : ($document->receivers ?? collect());
                    @endphp
                    <div id="mode-by_region" class="receiver-block hidden">
                        <div class="byregion-head">
                            <div class="byregion-title"><i class="bi bi-geo-alt-fill region-icon"></i> <span data-i18n="byRegionTitle">Получатели по региону</span></div>
                            <span class="byregion-badge" id="byregionBadge">{{ $carriedUsers->count() }}</span>
                        </div>

                        <div class="carried-list {{ $carriedUsers->count() ? '' : 'hidden' }}" id="byregionList">
                            @foreach($carriedUsers as $u)
                            @php $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($u->name ?? '?', 0, 1)); @endphp
                            <div class="carried-item" data-uid="{{ $u->id }}">
                                <div class="carried-avatar">{{ $initial }}</div>
                                <div class="carried-info">
                                    <div class="carried-name">{{ $u->name }}</div>
                                    <div class="carried-meta">
                                        @if($u->email)<span><i class="bi bi-envelope"></i> {{ $u->email }}</span>@endif
                                        @if($u->phone)<span><i class="bi bi-telephone"></i> {{ $u->phone }}</span>@endif
                                    </div>
                                </div>
                                <button type="button" class="carried-remove" data-uid="{{ $u->id }}" title="Удалить"><i class="bi bi-x-lg"></i></button>
                            </div>
                            @endforeach
                        </div>

                        <div class="byregion-empty {{ $carriedUsers->count() ? 'hidden' : '' }}" id="byregionEmpty" data-i18n="byRegionEmpty">
                            Получатели ещё не выбраны. Нажмите кнопку ниже, чтобы выбрать по области, городу и организации.
                        </div>

                        <a href="{{ route('documents.recipients.create', ['return_url' => route('documents.edit', $document->id)]) }}" class="byregion-add-btn">
                            <i class="bi bi-plus-circle-fill"></i>
                            <span data-i18n="byRegionAdd">{{ $carriedUsers->count() ? 'Изменить / добавить' : 'Выбрать получателей по региону' }}</span>
                        </a>

                        <p class="byregion-error" id="byregion-error">
                            ⚠ <span data-i18n="byRegionError">Выберите хотя бы одного получателя по региону</span>
                        </p>
                    </div>

                    @php
                        $selectedDeptsCount = $selectedDepartments->count() ?? 0;
                    @endphp
                    <div id="mode-by_department" class="receiver-block hidden">
                        <div class="byregion-head">
                            <div class="byregion-title">
                                <i class="bi bi-building-fill dept-icon"></i> 
                                <span data-i18n="byDepartmentTitle">Выбор по отделам</span>
                            </div>
                            <span class="byregion-badge dept-badge" id="byDepartmentBadge">{{ $selectedDeptsCount }}</span>
                        </div>

                        <div class="carried-list {{ $selectedDeptsCount ? '' : 'hidden' }}" id="byDepartmentList">
                            @foreach(($selectedDepartments ?? collect()) as $dept)
                                @php $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($dept->name ?? '?', 0, 1)); @endphp
                                <div class="carried-item dept-item" data-dept-id="{{ $dept->id }}">
                                    <div class="carried-avatar">{{ $initial }}</div>
                                    <div class="carried-info">
                                        <div class="carried-name">{{ $dept->name }}</div>
                                        <div class="carried-meta">
                                            <span><i class="bi bi-building"></i> L{{ $dept->level }}</span>
                                            <span><i class="bi bi-people-fill"></i> {{ $dept->users->count() }} чел.</span>
                                        </div>
                                    </div>
                                    <button type="button" class="carried-remove" data-dept-id="{{ $dept->id }}" title="Удалить"><i class="bi bi-x-lg"></i></button>
                                </div>
                            @endforeach
                        </div>

                        <div class="byregion-empty dept-empty {{ $selectedDeptsCount ? 'hidden' : '' }}" id="byDepartmentEmpty" data-i18n="byDepartmentEmpty">
                            Отделы ещё не выбраны. Нажмите кнопку ниже, чтобы выбрать отделы.
                        </div>

                        <a href="{{ route('documents.select-by-department', ['return_url' => route('documents.edit', $document->id)]) }}" class="byregion-add-btn dept-add-btn">
                            <i class="bi bi-folder-plus"></i>
                            <span data-i18n="byDepartmentAdd">{{ $selectedDeptsCount ? 'Изменить отделы' : 'Выбрать отделы' }}</span>
                        </a>

                        <p class="byregion-error" id="bydepartment-error">
                             <span data-i18n="byDepartmentError">Выберите хотя бы один отдел</span>
                        </p>
                    </div>

                    @php
                        $selCompanyId = session('selected_company_id', $document->receiver_company_id ?? null);
                        $selCompany = $selCompanyId ? \App\Models\Company::find($selCompanyId) : null;
                        $selCompanyUsers = session('selected_company_users', $document->receiver_company_user_ids ?? []);
                        $companyUserCount = is_array($selCompanyUsers) ? count($selCompanyUsers) : 0;
                    @endphp
                    <div id="mode-by_company" class="receiver-block hidden">
                        <div class="byregion-head">
                            <div class="byregion-title">
                                <i class="bi bi-diagram-3-fill company-icon"></i> 
                                <span data-i18n="byCompanyTitle">Выбор из дерева компаний</span>
                            </div>
                            <span class="byregion-badge company-badge" id="byCompanyBadge">{{ $companyUserCount }}</span>
                        </div>

                        <div class="carried-list {{ $companyUserCount ? '' : 'hidden' }}" id="byCompanyList">
                            @if($selCompany)
                                <div class="carried-item company-item" data-company-id="{{ $selCompany->id }}">
                                    <div class="carried-avatar">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($selCompany->name ?? '?', 0, 1)) }}
                                    </div>
                                    <div class="carried-info">
                                        <div class="carried-name">{{ $selCompany->name }}</div>
                                        <div class="carried-meta">
                                            <span><i class="bi bi-building"></i> Выбрано: {{ $companyUserCount }} чел.</span>
                                        </div>
                                    </div>
                                    <button type="button" class="carried-remove" data-company-id="{{ $selCompany->id }}" title="Очистить выбор">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            @endif
                        </div>

                        <div class="byregion-empty company-empty {{ $companyUserCount ? 'hidden' : '' }}" id="byCompanyEmpty" data-i18n="byCompanyEmpty">
                            Компания и сотрудники ещё не выбраны. Нажмите кнопку ниже.
                        </div>

                        <a href="{{ route('documents.select-by-company', ['return_url' => route('documents.edit', $document->id)]) }}" class="byregion-add-btn company-add-btn">
                            <i class="bi bi-folder-plus"></i>
                            <span data-i18n="byCompanyAdd">{{ $companyUserCount ? 'Изменить выбор' : 'Выбрать компанию и сотрудников' }}</span>
                        </a>

                        <p class="byregion-error" id="bycompany-error">
                             <span data-i18n="byCompanyError">Выберите компанию и хотя бы одного сотрудника</span>
                        </p>
                    </div>
                </div>

                <div class="action-row">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-save-fill"></i>
                        <span data-i18n="submitButton">Сохранить изменения</span>
                    </button>
                    <button type="button" class="btn-delete" id="deleteDocumentBtn">
                        <i class="bi bi-trash-fill"></i>
                        <span data-i18n="deleteButton">Удалить документ</span>
                    </button>
                </div>
            </form>

            <form action="{{ route('documents.destroy', $document->id) }}" method="POST" id="deleteForm" style="display:none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const FORM_STORAGE_KEY = 'docsign_draft_edit_form_{{ $document->id }}';
        const UPLOAD_TEMP_URL = '{{ route("documents.upload-temp") }}';
        const DEPT_SYNC_URL = '{{ route("documents.select-by-department.store") }}';
        const COMP_SYNC_URL = '{{ route("documents.select-by-company.store") }}';
        const DOCUMENT_ID = {{ $document->id }};

        const translations = {
            ru: {
                back: "Назад", errorTitle: "Ошибка при обновлении документа", pageTitle: "Редактирование документа",
                pageSubtitle: "Измените информацию о документе", labelNumber: "Номер документа", labelType: "Тип документа",
                labelStatus: "Статус документа", labelDeadline: "Дедлайн", labelTitle: "Заголовок", labelDescription: "Описание",
                labelFile: "Файл документа", labelReceiverMode: "Способ отправки", modeAllTeam: "Всей команде",
                modeAllTeamDesc: "Всем участникам", modeAllTeamDescNoCompany: "Требуется компания", modeSelectTeam: "Выбрать",
                modeSelectTeamDesc: "До 5 человек", modeOtherCompany: "Другая команда", modeOtherCompanyDesc: "Внешний получатель",
                modeByRegion: "По региону", modeByRegionDesc: "Выбор по области/городу",
                modeByDepartment: "По отделам", modeByDepartmentDesc: "Выбор из отделов",
                modeByCompany: "Из дерева компаний", modeByCompanyDesc: "Выбрать компанию и сотрудников",
                allTeamInfo: "Отправка всем участникам", receiversCount: "Получателей:",
                selectReceiversLabel: "Выберите получателей (до 5)", searchPlaceholder: "Введите имя, email или телефон...",
                selectedPlaceholder: "Выбранные пользователи появятся здесь...", selectError: "Выберите хотя бы одного получателя",
                searchCompanyLabel: "Поиск получателя из другой команды", submitButton: "Сохранить изменения",
                deleteButton: "Удалить документ", deleteConfirm: "Вы уверены, что хотите удалить этот документ? Это действие необратимо.",
                filePlaceholder: "Выберите файл...", filePlaceholderReplace: "Заменить файл...", usersNotFound: "Пользователи не найдены", maxReceivers: "Максимум 5 получателей",
                alertSelectMode: "Выберите способ отправки документа", alertSelectCompany: "Выберите получателя из другой команды",
                numberPlaceholder: "№ 001", typePlaceholder: "Договор, Акт...", titlePlaceholder: "Название документа",
                descriptionPlaceholder: "Краткое описание документа...", statusSend: "Отправить на подпись", statusDraft: "Сохранить как черновик",
                byRegionTitle: "Получатели по региону", byRegionEmpty: "Получатели ещё не выбраны. Нажмите кнопку ниже, чтобы выбрать по области, городу и организации.",
                byRegionAdd: "Выбрать получателей по региону", byRegionChange: "Изменить / добавить", byRegionError: "Выберите хотя бы одного получателя по региону",
                byDepartmentTitle: "Выбор по отделам", byDepartmentEmpty: "Отделы ещё не выбраны. Нажмите кнопку ниже, чтобы выбрать отделы.",
                byDepartmentAdd: "Выбрать отделы", byDepartmentChange: "Изменить отделы", byDepartmentError: "Выберите хотя бы один отдел",
                byCompanyTitle: "Выбор из дерева компаний", byCompanyEmpty: "Компания и сотрудники ещё не выбраны. Нажмите кнопку ниже.",
                byCompanyAdd: "Выбрать компанию и сотрудников", byCompanyChange: "Изменить выбор", byCompanyError: "Выберите компанию и хотя бы одного сотрудника",
                fileRequiredError: "❌ Файл не выбран! Пожалуйста, прикрепите файл.",
                fileUploading: "⏳ Загружаем файл на сервер...",
                fileUploaded: "✓ Файл загружен на сервер. Можно переходить к выбору получателей.",
                fileRestored: "✓ Файл уже загружен на сервер. Не нужно выбирать заново.",
                aiTitle: "ИИ Помощник редактирования", aiSubtitle: "Опиши изменения — ИИ поможет обновить документ",
                aiGenerate: "Применить изменения с ИИ", aiStatus: "ИИ обрабатывает изменения...",
                aiQuestionsTitle: "ИИ задаёт уточняющие вопросы:", aiSubmitAnswers: "Отправить ответы",
                aiResultTitle: "Изменения применены!", aiDownload: "Скачать обновлённый документ"
            },
            tj: {
                back: "Бозгашт", errorTitle: "Хато ҳангоми навсозии ҳуҷҷат", pageTitle: "Таҳрири ҳуҷҷат",
                pageSubtitle: "Маълумотро оид ба ҳуҷҷат тағйир диҳед", labelNumber: "Рақами ҳуҷҷат", labelType: "Намуди ҳуҷҷат",
                labelStatus: "Ҳолати ҳуҷҷат", labelDeadline: "Мӯҳлат", labelTitle: "Сарлавҳа", labelDescription: "Тавсиф",
                labelFile: "Файли ҳуҷҷат", labelReceiverMode: "Усули фиристодан", modeAllTeam: "Ба ҳамаи даста",
                modeAllTeamDesc: "Ба ҳамаи иштирокчиён", modeAllTeamDescNoCompany: "Ширкат лозим аст", modeSelectTeam: "Интихоб кардан",
                modeSelectTeamDesc: "То 5 нафар", modeOtherCompany: "Дигар даста", modeOtherCompanyDesc: "Гирандаи берунӣ",
                modeByRegion: "Аз рӯи вилоят", modeByRegionDesc: "Интихоб аз рӯи минтақа",
                modeByDepartment: "Аз рӯи шӯъбаҳо", modeByDepartmentDesc: "Интихоб аз шӯъбаҳо",
                modeByCompany: "Аз дарахти ширкатҳо", modeByCompanyDesc: "Интихоби ширкат ва кормандон",
                allTeamInfo: "Фиристодан ба ҳамаи иштирокчиён", receiversCount: "Гирандаҳо:",
                selectReceiversLabel: "Гирандаҳоро интихоб кунед (то 5)", searchPlaceholder: "Ном, email ё телефонро ворид кунед...",
                selectedPlaceholder: "Корбарони интихобшуда дар ин ҷо пайдо мешаванд...", selectError: "Ҳадди ақал як гирандаро интихоб кунед",
                searchCompanyLabel: "Ҷустуҷӯи гиранда аз дигар даста", submitButton: "Нигоҳ доштани тағйирот",
                deleteButton: "Нест кардани ҳуҷҷат", deleteConfirm: "Оё шумо мутмаин ҳастед, ки мехоҳед ин ҳуҷҷатро нест кунед? Ин амал барнагардад.",
                filePlaceholder: "Файлро интихоб кунед...", filePlaceholderReplace: "Иваз кардани файл...", usersNotFound: "Корбарон ёфт нашуданд", maxReceivers: "Ҳадди аксар 5 гиранда",
                alertSelectMode: "Усули фиристодани ҳуҷҷатро интихоб кунед", alertSelectCompany: "Гирандаро аз дигар даста интихоб кунед",
                numberPlaceholder: "№ 001", typePlaceholder: "Шартнома, Акт...", titlePlaceholder: "Номи ҳуҷҷат",
                descriptionPlaceholder: "Тавсифи мухтасари ҳуҷҷат...", statusSend: "Барои имзо фиристодан", statusDraft: "Ҳамчун пешнавис нигоҳ доштан",
                byRegionTitle: "Гирандагон аз рӯи вилоят", byRegionEmpty: "Гирандагон ҳанӯз интихоб нашудаанд. Тугмаи поёнро пахш кунед.",
                byRegionAdd: "Интихоб кардан аз рӯи вилоят", byRegionChange: "Тағйир додан / илова кардан", byRegionError: "Ҳадди ақал як гирандаро аз рӯи вилоят интихоб кунед",
                byDepartmentTitle: "Интихоб аз рӯи шӯъбаҳо", byDepartmentEmpty: "Шӯъбаҳо ҳанӯз интихоб нашудаанд. Тугмаи поёнро пахш кунед.",
                byDepartmentAdd: "Интихоби шӯъбаҳо", byDepartmentChange: "Тағйир додани шӯъбаҳо", byDepartmentError: "Ҳадди ақал як шӯъбаро интихоб кунед",
                byCompanyTitle: "Интихоб аз дарахти ширкатҳо", byCompanyEmpty: "Ширкат ва кормандон ҳанӯз интихоб нашудаанд. Тугмаи поёнро пахш кунед.",
                byCompanyAdd: "Интихоби ширкат ва кормандон", byCompanyChange: "Тағйир додани интихоб", byCompanyError: "Ширкат ва ҳадди ақал як кормандро интихоб кунед",
                fileRequiredError: "❌ Файл интихоб нашудааст! Лутфан, файлро замима кунед.",
                fileUploading: "⏳ Файл ба сервер бор шуда истодааст...",
                fileUploaded: "✓ Файл ба сервер бор шуд. Метавонед гирандаҳоро интихоб кунед.",
                fileRestored: "✓ Файл аллакай дар сервер аст. Аз нав интихоб кардан лозим нест.",
                aiTitle: "Ёрдамчии ИИ барои таҳрир", aiSubtitle: "Тағйиротро тавсиф кунед — ИИ кӯмак мекунад",
                aiGenerate: "Тағйиротро бо ИИ истифода баред", aiStatus: "ИИ тағйиротро коркард мекунад...",
                aiQuestionsTitle: "ИИ саволҳои иловагӣ медиҳад:", aiSubmitAnswers: "Фиристодани ҷавобҳо",
                aiResultTitle: "Тағйирот истифода шуд!", aiDownload: "Боргирӣ кардани ҳуҷҷати нав"
            },
            en: {
                back: "Back", errorTitle: "Error updating document", pageTitle: "Edit Document",
                pageSubtitle: "Modify document information", labelNumber: "Document Number", labelType: "Document Type",
                labelStatus: "Document Status", labelDeadline: "Deadline", labelTitle: "Title", labelDescription: "Description",
                labelFile: "Document File", labelReceiverMode: "Sending Method", modeAllTeam: "All Team",
                modeAllTeamDesc: "To all members", modeAllTeamDescNoCompany: "Company required", modeSelectTeam: "Select",
                modeSelectTeamDesc: "Up to 5 people", modeOtherCompany: "Other Team", modeOtherCompanyDesc: "External recipient",
                modeByRegion: "By Region", modeByRegionDesc: "Select by region/city",
                modeByDepartment: "By Department", modeByDepartmentDesc: "Select from departments",
                modeByCompany: "From Company Tree", modeByCompanyDesc: "Select company and employees",
                allTeamInfo: "Sending to all members", receiversCount: "Recipients:",
                selectReceiversLabel: "Select recipients (up to 5)", searchPlaceholder: "Enter name, email or phone...",
                selectedPlaceholder: "Selected users will appear here...", selectError: "Select at least one recipient",
                searchCompanyLabel: "Search recipient from another team", submitButton: "Save Changes",
                deleteButton: "Delete Document", deleteConfirm: "Are you sure you want to delete this document? This action cannot be undone.",
                filePlaceholder: "Choose file...", filePlaceholderReplace: "Replace file...", usersNotFound: "Users not found", maxReceivers: "Maximum 5 recipients",
                alertSelectMode: "Select document sending method", alertSelectCompany: "Select recipient from another team",
                numberPlaceholder: "No. 001", typePlaceholder: "Contract, Act...", titlePlaceholder: "Document name",
                descriptionPlaceholder: "Brief document description...", statusSend: "Send for signature", statusDraft: "Save as draft",
                byRegionTitle: "Recipients by region", byRegionEmpty: "No recipients selected yet. Click the button below.",
                byRegionAdd: "Select recipients by region", byRegionChange: "Change / add", byRegionError: "Select at least one recipient by region",
                byDepartmentTitle: "Selection by department", byDepartmentEmpty: "No departments selected yet. Click the button below.",
                byDepartmentAdd: "Select departments", byDepartmentChange: "Change departments", byDepartmentError: "Select at least one department",
                byCompanyTitle: "Selection from company tree", byCompanyEmpty: "Company and employees not selected yet. Click the button below.",
                byCompanyAdd: "Select company and employees", byCompanyChange: "Change selection", byCompanyError: "Select a company and at least one employee",
                fileRequiredError: "❌ File not selected! Please attach a file.",
                fileUploading: "⏳ Uploading file to server...",
                fileUploaded: "✓ File uploaded to server. You can now select recipients.",
                fileRestored: "✓ File already on server. No need to select again.",
                aiTitle: "AI Edit Assistant", aiSubtitle: "Describe changes — AI will help update the document",
                aiGenerate: "Apply changes with AI", aiStatus: "AI is processing changes...",
                aiQuestionsTitle: "AI asks clarifying questions:", aiSubmitAnswers: "Submit answers",
                aiResultTitle: "Changes applied!", aiDownload: "Download updated document"
            }
        };

        function getCurrentLang() {
            return localStorage.getItem('docsign_lang') || localStorage.getItem('app-lang') || 'ru';
        }

        function applyTranslations() {
            const lang = getCurrentLang();
            const t = translations[lang] || translations['ru'];
            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (t[key] !== undefined) el.textContent = t[key];
            });
            document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
                const key = el.getAttribute('data-i18n-placeholder');
                if (t[key] !== undefined) el.setAttribute('placeholder', t[key]);
            });
            return t;
        }

        let currentT = applyTranslations();
        window.addEventListener('docsign:lang-changed', function(e) {
            if (e.detail && e.detail.lang) {
                localStorage.setItem('docsign_lang', e.detail.lang);
                localStorage.setItem('app-lang', e.detail.lang);
            }
            currentT = applyTranslations();
        });

        function getCsrfToken() {
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) return metaTag.getAttribute('content');
            const csrfInput = document.querySelector('input[name="_token"]');
            if (csrfInput) return csrfInput.value;
            const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
            return match ? decodeURIComponent(match[1]) : '';
        }

        function clearCompanySession() {
            return fetch(COMP_SYNC_URL, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': getCsrfToken(), 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ 
                    company_id: '', 
                    user_ids: '',
                    return_url: window.location.href
                })
            });
        }

        function saveFormToStorage() {
            const tempPath = document.getElementById('temp-file-path');
            const formData = {
                number: document.getElementById('field-number').value,
                type: document.getElementById('field-type').value,
                status: document.getElementById('field-status').value,
                deadline: document.getElementById('field-deadline').value,
                title: document.getElementById('field-title').value,
                content: document.getElementById('field-content').value,
                tempFilePath: tempPath ? tempPath.value : '',
                fileName: localStorage.getItem('docsign_last_file_name_edit_' + DOCUMENT_ID) || '',
                timestamp: Date.now()
            };
            localStorage.setItem(FORM_STORAGE_KEY, JSON.stringify(formData));
        }

        function restoreFormFromStorage() {
            const savedData = localStorage.getItem(FORM_STORAGE_KEY);
            if (!savedData) return;
            try {
                const data = JSON.parse(savedData);
                const hoursPassed = (Date.now() - data.timestamp) / (1000 * 60 * 60);
                if (hoursPassed > 2) { localStorage.removeItem(FORM_STORAGE_KEY); return; }

                if (data.number) document.getElementById('field-number').value = data.number;
                if (data.type) document.getElementById('field-type').value = data.type;
                if (data.status) document.getElementById('field-status').value = data.status;
                if (data.deadline) document.getElementById('field-deadline').value = data.deadline;
                if (data.title) document.getElementById('field-title').value = data.title;
                if (data.content) document.getElementById('field-content').value = data.content;

                if (data.tempFilePath && data.fileName) {
                    const tempPath = document.getElementById('temp-file-path');
                    const fileNameEl = document.getElementById('file-name');
                    const uploadBox = document.getElementById('file-upload-box');
                    const statusEl = document.getElementById('file-upload-status');
                    const t = translations[getCurrentLang()] || translations['ru'];

                    tempPath.value = data.tempFilePath;
                    fileNameEl.innerHTML = `<i class="bi bi-file-earmark-check" style="color:#22c55e; margin-right:6px;"></i> <strong>${data.fileName}</strong> <span style="color:#22c55e; font-size:10px;">✓</span>`;
                    fileNameEl.style.color = '#fff';
                    uploadBox.style.borderColor = 'rgba(34, 197, 94, 0.6)';
                    uploadBox.style.background = 'rgba(34, 197, 94, 0.05)';
                    statusEl.style.display = 'block';
                    statusEl.innerHTML = `<span style="color:#22c55e;">${t.fileRestored}</span>`;
                }
            } catch (e) { console.error('DocSign restore error:', e); }
        }

        function clearFormStorage() {
            localStorage.removeItem(FORM_STORAGE_KEY);
            localStorage.removeItem('docsign_last_file_name_edit_' + DOCUMENT_ID);
        }

        const teamUsers = @json($teamUsersArray ?? []);
        const otherUsers = @json($otherUsersArray ?? []);
        const CARRIED_IDS = @json(session('selected_recipients') ?? $document->receiver_ids ?? []);
        const INITIAL_OTHER_RECEIVER = @json($document->other_receiver_id ?? null);
        const INITIAL_MODE = @json($document->receiver_mode ?? '');

        let selectedTeam = [];
        let activeMode = INITIAL_MODE || '';
        let carriedRemaining = new Set((CARRIED_IDS || []).map(id => String(id)));

        const modeBtns = document.querySelectorAll('.mode-btn');
        const modeBlocks = document.querySelectorAll('.receiver-block');
        const modeInput = document.getElementById('receiver_mode');
        const teamReceivers = document.getElementById('team_receivers');
        const teamError = document.getElementById('team-error');
        const byregionError = document.getElementById('byregion-error');
        const bydepartmentError = document.getElementById('bydepartment-error');
        const bycompanyError = document.getElementById('bycompany-error');

        function syncTeamReceivers() {
            const ids = selectedTeam.map(u => String(u.id)).concat(Array.from(carriedRemaining));
            const uniqueIds = [...new Set(ids)];
            if (teamReceivers) teamReceivers.value = uniqueIds.join(',');
        }

        modeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                if (this.disabled) return;
                modeBtns.forEach(b => b.classList.remove('active'));
                modeBlocks.forEach(b => b.classList.add('hidden'));
                this.classList.add('active');
                
                const mode = this.dataset.mode;
                activeMode = mode;
                modeInput.value = mode; 
               
                const block = document.getElementById('mode-' + mode);
                if (block) block.classList.remove('hidden');
                
                if (teamError) teamError.style.display = 'none';
                if (byregionError) byregionError.style.display = 'none';
                if (bydepartmentError) bydepartmentError.style.display = 'none';
                if (bycompanyError) bycompanyError.style.display = 'none';
                syncTeamReceivers();
            });
        });

        // Активируем исходный режим документа
        if (activeMode) {
            const activeBtn = document.querySelector('.mode-btn[data-mode="' + activeMode + '"]');
            if (activeBtn) {
                activeBtn.classList.add('active');
                const block = document.getElementById('mode-' + activeMode);
                if (block) block.classList.remove('hidden');
            }
        }

        if (carriedRemaining.size > 0 && !activeMode) {
            activeMode = 'by_region';
            modeInput.value = 'by_region';
            const brBtn = document.querySelector('.mode-btn[data-mode="by_region"]');
            if (brBtn) brBtn.classList.add('active');
            const brBlock = document.getElementById('mode-by_region');
            if (brBlock) brBlock.classList.remove('hidden');
            syncTeamReceivers();
        }

        const initialDeptCount = parseInt(document.getElementById('byDepartmentBadge')?.textContent || '0');
        if (initialDeptCount > 0 && !activeMode) {
            activeMode = 'by_department';
            modeInput.value = 'by_department';
            const bdBtn = document.querySelector('.mode-btn[data-mode="by_department"]');
            if (bdBtn) bdBtn.classList.add('active');
            const bdBlock = document.getElementById('mode-by_department');
            if (bdBlock) bdBlock.classList.remove('hidden');
        }

        const initialCompanyCount = parseInt(document.getElementById('byCompanyBadge')?.textContent || '0');
        if (initialCompanyCount > 0 && !activeMode) {
            activeMode = 'by_company';
            modeInput.value = 'by_company';
            const bcBtn = document.querySelector('.mode-btn[data-mode="by_company"]');
            if (bcBtn) bcBtn.classList.add('active');
            const bcBlock = document.getElementById('mode-by_company');
            if (bcBlock) bcBlock.classList.remove('hidden');
        }

        const fileInput = document.getElementById('file-input');
        const fileName = document.getElementById('file-name');
        const tempFilePath = document.getElementById('temp-file-path');
        const fileUploadStatus = document.getElementById('file-upload-status');
        const fileUploadBox = document.getElementById('file-upload-box');
        const existingFileBox = document.getElementById('existing-file-box');
        const removeExistingFile = document.getElementById('remove_existing_file');

        // Удаление существующего файла
        const removeExistingBtn = document.getElementById('remove-existing-file');
        if (removeExistingBtn) {
            removeExistingBtn.addEventListener('click', function() {
                const t = translations[getCurrentLang()] || translations['ru'];
                if (!confirm(t.deleteConfirm || 'Удалить файл?')) return;
                
                if (existingFileBox) existingFileBox.remove();
                if (removeExistingFile) removeExistingFile.value = '1';
                
                fileName.textContent = t.filePlaceholder;
                fileName.style.color = '#8892a6';
                saveFormToStorage();
            });
        }

        if (fileInput) {
            fileInput.addEventListener('change', function() {
                const t = translations[getCurrentLang()] || translations['ru'];
                if (this.files.length === 0) {
                    fileName.textContent = existingFileBox ? t.filePlaceholderReplace : t.filePlaceholder;
                    fileName.style.color = '#8892a6';
                    tempFilePath.value = '';
                    fileUploadStatus.style.display = 'none';
                    fileUploadBox.style.borderColor = 'rgba(255,255,255,0.15)';
                    fileUploadBox.style.background = 'rgba(255,255,255,0.03)';
                    localStorage.removeItem('docsign_last_file_name_edit_' + DOCUMENT_ID);
                    saveFormToStorage();
                    return;
                }
                const file = this.files[0];
                fileName.innerHTML = `<i class="bi bi-hourglass-split" style="color:#4f8cff; margin-right:6px;"></i> ${file.name}`;
                fileName.style.color = '#4f8cff';
                fileUploadBox.style.borderColor = 'rgba(79, 140, 255, 0.6)';
                fileUploadBox.style.background = 'rgba(79, 140, 255, 0.05)';
                fileUploadStatus.style.display = 'block';
                fileUploadStatus.innerHTML = `<span style="color:#4f8cff;">${t.fileUploading}</span>`;

                const formData = new FormData();
                formData.append('file_path', file);

                fetch(UPLOAD_TEMP_URL, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                })
                .then(res => {
                    if (!res.ok) return res.json().then(err => { throw new Error(err.message || 'Ошибка загрузки'); });
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        tempFilePath.value = data.temp_path;
                        localStorage.setItem('docsign_last_file_name_edit_' + DOCUMENT_ID, data.file_name);
                        fileName.innerHTML = `<i class="bi bi-file-earmark-check" style="color:#22c55e; margin-right:6px;"></i> <strong>${data.file_name}</strong> <span style="color:#22c55e; font-size:10px;">✓</span>`;
                        fileName.style.color = '#fff';
                        fileUploadBox.style.borderColor = 'rgba(34, 197, 94, 0.6)';
                        fileUploadBox.style.background = 'rgba(34, 197, 94, 0.05)';
                        fileUploadStatus.innerHTML = `<span style="color:#22c55e;">${t.fileUploaded}</span>`;
                        saveFormToStorage();
                    }
                })
                .catch(error => {
                    fileName.innerHTML = `<span style="color:#ff6b6b;">❌ ${error.message}</span>`;
                    fileName.style.color = '#ff6b6b';
                    fileUploadBox.style.borderColor = '#ff6b6b';
                    fileUploadBox.style.background = 'rgba(255, 107, 107, 0.05)';
                    fileUploadStatus.innerHTML = `<span style="color:#ff6b6b;">❌ ${error.message}</span>`;
                    tempFilePath.value = '';
                });
            });
        }

        const form = document.getElementById('documentForm');

        form.addEventListener('submit', async function(e) {
            const t = translations[getCurrentLang()] || translations['ru'];
            let hasError = false;

            if (!activeMode) {
                e.preventDefault(); alert(t.alertSelectMode); hasError = true;
            }

            if (!hasError && activeMode === 'by_region' && carriedRemaining.size === 0) {
                e.preventDefault();
                if (byregionError) byregionError.style.display = 'block';
                hasError = true;
            }

            if (!hasError && activeMode === 'by_department') {
                const deptBadge = document.getElementById('byDepartmentBadge');
                const count = deptBadge ? parseInt(deptBadge.textContent) : 0;
                if (count === 0) {
                    e.preventDefault();
                    if (bydepartmentError) bydepartmentError.style.display = 'block';
                    hasError = true;
                }
            }

            if (!hasError && activeMode === 'by_company') {
                const compBadge = document.getElementById('byCompanyBadge');
                const count = compBadge ? parseInt(compBadge.textContent) : 0;
                if (count === 0) {
                    e.preventDefault();
                    if (bycompanyError) bycompanyError.style.display = 'block';
                    hasError = true;
                }
            }

            if (!hasError && activeMode === 'select_team') {
                if (selectedTeam.length === 0) {
                    e.preventDefault();
                    if (teamError) teamError.style.display = 'block';
                    hasError = true;
                }
            }

            if (!hasError && activeMode === 'other_company') {
                const cr = document.getElementById('company_receiver');
                if (!cr || !cr.value) { e.preventDefault(); alert(t.alertSelectCompany); hasError = true; }
            }

            const hasFileInInput = fileInput && fileInput.files.length > 0;
            const hasTempFile = tempFilePath && tempFilePath.value !== '';
            const hasExistingFile = existingFileBox && removeExistingFile && removeExistingFile.value !== '1';

            if (!hasError && !hasFileInInput && !hasTempFile && !hasExistingFile) {
                e.preventDefault(); hasError = true;
                fileName.innerHTML = `<span style="color:#ff6b6b; font-weight:700;">${t.fileRequiredError}</span>`;
                fileUploadBox.style.borderColor = '#ff6b6b';
                fileUploadBox.style.background = 'rgba(255, 107, 107, 0.1)';
                fileUploadBox.style.animation = 'shake 0.5s';
                fileUploadBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            if (!hasError) {
                try {
                    await clearCompanySession();
                    console.log('✓ Сессия компании очищена перед обновлением документа');
                } catch (err) {
                    console.warn('⚠ Не удалось очистить сессию компании:', err);
                }
                
                clearFormStorage();
            }
        });

        const formInputs = document.querySelectorAll('#documentForm input:not([type="file"]), #documentForm select, #documentForm textarea');
        formInputs.forEach(el => {
            el.addEventListener('change', saveFormToStorage);
            el.addEventListener('input', saveFormToStorage);
        });

        restoreFormFromStorage();

        const teamSearch = document.getElementById('team-search');
        const teamList = document.getElementById('team-list');
        const teamSelected = document.getElementById('team-selected');

        if (teamSearch && teamList) {
            teamSearch.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                if (query.length < 2) { teamList.classList.add('hidden'); return; }
                const t = translations[getCurrentLang()] || translations['ru'];
                const filtered = teamUsers.filter(user => {
                    if (selectedTeam.find(s => s.id === user.id)) return false;
                    const name = (user.name || '').toLowerCase();
                    const email = (user.email || '').toLowerCase();
                    const phone = (user.phone || '').toLowerCase();
                    const company = (user.company || user.company_name || '').toLowerCase();
                    return name.includes(query) || email.includes(query) || phone.includes(query) || company.includes(query);
                });
                teamList.innerHTML = '';
                if (filtered.length === 0) {
                    teamList.innerHTML = `<div class="dropdown-empty">${t.usersNotFound}</div>`;
                } else {
                    filtered.forEach(user => {
                        const item = document.createElement('div');
                        item.className = 'dropdown-item';
                        const company = user.company || user.company_name || '';
                        const phoneDisplay = user.phone ? `<span style="color:#8892a6; display:flex; align-items:center; gap:4px;"><i class="bi bi-telephone" style="font-size:10px"></i> ${user.phone}</span>` : '';
                        item.innerHTML = `<div><span class="name">${user.name}</span><div class="meta">${company ? `<span class="company">${company}</span>` : ''}<span>${user.email || ''}</span>${phoneDisplay}</div></div><i class="bi bi-plus-circle-fill add-icon"></i>`;
                        item.addEventListener('click', function(e) {
                            e.stopPropagation();
                            const t2 = translations[getCurrentLang()] || translations['ru'];
                            if (selectedTeam.length >= 5) { alert(t2.maxReceivers); return; }
                            selectedTeam.push(user);
                            updateTeamSelected();
                            teamSearch.value = '';
                            teamList.classList.add('hidden');
                            teamSearch.focus();
                        });
                        teamList.appendChild(item);
                    });
                }
                teamList.classList.remove('hidden');
            });

            document.addEventListener('click', function(e) {
                if (!teamSearch.contains(e.target) && !teamList.contains(e.target)) teamList.classList.add('hidden');
            });

            function updateTeamSelected() {
                const t = translations[getCurrentLang()] || translations['ru'];
                teamSelected.innerHTML = '';
                if (selectedTeam.length === 0) {
                    teamSelected.innerHTML = `<span style="font-size:10px;color:#8892a6;">${t.selectedPlaceholder}</span>`;
                } else {
                    selectedTeam.forEach(user => {
                        const chip = document.createElement('span');
                        chip.className = 'chip';
                        chip.innerHTML = `${user.name} <button type="button" data-id="${user.id}">&times;</button>`;
                        chip.querySelector('button').addEventListener('click', function() {
                            selectedTeam = selectedTeam.filter(u => u.id !== user.id);
                            updateTeamSelected();
                        });
                        teamSelected.appendChild(chip);
                    });
                }
                syncTeamReceivers();
                if (teamError) teamError.style.display = 'none';
            }
        }

        const companySearch = document.getElementById('company-search');
        const companyList = document.getElementById('company-list');
        const companySelected = document.getElementById('company-selected');
        const companyReceiver = document.getElementById('company_receiver');
        let selectedCompany = null;

        // Восстановление внешнего получателя при загрузке
        if (INITIAL_OTHER_RECEIVER && companySelected) {
            const foundUser = otherUsers.find(u => String(u.id) === String(INITIAL_OTHER_RECEIVER));
            if (foundUser) {
                selectedCompany = foundUser;
                companyReceiver.value = foundUser.id;
                companySelected.innerHTML = `<span class="chip">${foundUser.name} <button type="button" id="clear-company">&times;</button></span>`;
                document.getElementById('clear-company')?.addEventListener('click', function() {
                    selectedCompany = null; companyReceiver.value = ''; companySelected.innerHTML = '';
                });
            }
        }

        if (companySearch && companyList) {
            companySearch.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                if (query.length < 2) { companyList.classList.add('hidden'); return; }
                const t = translations[getCurrentLang()] || translations['ru'];
                const filtered = otherUsers.filter(user => {
                    const name = (user.name || '').toLowerCase();
                    const email = (user.email || '').toLowerCase();
                    const phone = (user.phone || '').toLowerCase();
                    const company = (user.company || user.company_name || '').toLowerCase();
                    return name.includes(query) || email.includes(query) || phone.includes(query) || company.includes(query);
                });
                companyList.innerHTML = '';
                if (filtered.length === 0) {
                    companyList.innerHTML = `<div class="dropdown-empty">${t.usersNotFound}</div>`;
                } else {
                    filtered.forEach(user => {
                        const item = document.createElement('div');
                        item.className = 'dropdown-item';
                        const company = user.company || user.company_name || '';
                        const phoneDisplay = user.phone ? `<span style="color:#8892a6; display:flex; align-items:center; gap:4px;"><i class="bi bi-telephone" style="font-size:10px"></i> ${user.phone}</span>` : '';
                        item.innerHTML = `<div><span class="name">${user.name}</span><div class="meta">${company ? `<span class="company">${company}</span>` : ''}<span>${user.email || ''}</span>${phoneDisplay}</div></div><i class="bi bi-check-circle-fill add-icon"></i>`;
                        item.addEventListener('click', function(e) {
                            e.stopPropagation();
                            selectedCompany = user;
                            companyReceiver.value = user.id;
                            companySelected.innerHTML = `<span class="chip">${user.name} <button type="button" id="clear-company">&times;</button></span>`;
                            document.getElementById('clear-company').addEventListener('click', function() {
                                selectedCompany = null; companyReceiver.value = ''; companySelected.innerHTML = '';
                            });
                            companySearch.value = '';
                            companyList.classList.add('hidden');
                        });
                        companyList.appendChild(item);
                    });
                }
                companyList.classList.remove('hidden');
            });
            document.addEventListener('click', function(e) {
                if (!companySearch.contains(e.target) && !companyList.contains(e.target)) companyList.classList.add('hidden');
            });
        }

        const byregionList = document.getElementById('byregionList');
        const byregionEmpty = document.getElementById('byregionEmpty');
        const byregionBadge = document.getElementById('byregionBadge');

        if (byregionList) {
            byregionList.querySelectorAll('.carried-remove').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const uid = String(this.dataset.uid);
                    carriedRemaining.delete(uid);
                    const item = this.closest('.carried-item');
                    if (item) item.remove();
                    const n = carriedRemaining.size;
                    if (byregionBadge) byregionBadge.textContent = n;
                    if (n === 0) {
                        if (byregionList) byregionList.classList.add('hidden');
                        if (byregionEmpty) byregionEmpty.classList.remove('hidden');
                    }
                    syncTeamReceivers();
                });
            });
        }

        const byDepartmentList = document.getElementById('byDepartmentList');
        const byDepartmentEmpty = document.getElementById('byDepartmentEmpty');
        const byDepartmentBadge = document.getElementById('byDepartmentBadge');

        if (byDepartmentList) {
            byDepartmentList.addEventListener('click', function(e) {
                const removeBtn = e.target.closest('.carried-remove');
                if (!removeBtn) return;

                const item = removeBtn.closest('.carried-item');
                if (item) item.remove();

                const remainingItems = byDepartmentList.querySelectorAll('.carried-item').length;
                if (byDepartmentBadge) byDepartmentBadge.textContent = remainingItems;

                if (remainingItems === 0) {
                    byDepartmentList.classList.add('hidden');
                    byDepartmentEmpty.classList.remove('hidden');
                    if (activeMode === 'by_department') {
                        activeMode = '';
                        modeInput.value = '';
                        document.querySelector('.mode-btn.active')?.classList.remove('active');
                        document.getElementById('mode-by_department')?.classList.add('hidden');
                    }
                }

                if (bydepartmentError) bydepartmentError.style.display = 'none';

                const remainingDeptIds = Array.from(byDepartmentList.querySelectorAll('.carried-item'))
                    .map(el => el.dataset.deptId)
                    .join(',');

                fetch(DEPT_SYNC_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' },
                    body: JSON.stringify({ selected_departments: remainingDeptIds, return_url: window.location.href })
                }).catch(err => console.error('Ошибка синхронизации сессии отделов:', err));
            });
        }

        const byCompanyList = document.getElementById('byCompanyList');
        const byCompanyEmpty = document.getElementById('byCompanyEmpty');
        const byCompanyBadge = document.getElementById('byCompanyBadge');

        if (byCompanyList) {
            byCompanyList.addEventListener('click', function(e) {
                const removeBtn = e.target.closest('.carried-remove');
                if (!removeBtn) return;
                
                e.preventDefault();
                e.stopPropagation();

                const item = removeBtn.closest('.carried-item');
                
                if (item) {
                    item.style.transition = 'all 0.2s ease';
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.95)';
                    setTimeout(() => item.remove(), 200);
                }

                if (byCompanyBadge) byCompanyBadge.textContent = '0';

                setTimeout(() => {
                    byCompanyList.classList.add('hidden');
                    if (byCompanyEmpty) byCompanyEmpty.classList.remove('hidden');
                    
                    const addBtnText = document.querySelector('#mode-by_company .company-add-btn span');
                    if (addBtnText) {
                        const t = translations[getCurrentLang()] || translations['ru'];
                        addBtnText.textContent = t.byCompanyAdd; 
                    }
                }, 200);

                if (activeMode === 'by_company') {
                    activeMode = '';
                    modeInput.value = '';
                    const activeBtn = document.querySelector('.mode-btn[data-mode="by_company"]');
                    if (activeBtn) activeBtn.classList.remove('active');
                    document.getElementById('mode-by_company').classList.add('hidden');
                }

                if (bycompanyError) bycompanyError.style.display = 'none';

                clearCompanySession()
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.success) {
                            console.log('✓ Сессия компании очищена на сервере');
                        }
                    })
                    .catch(err => {
                        console.error('Ошибка очистки сессии:', err);
                    });
            });
        }

        // Кнопка "Удалить документ"
        const deleteBtn = document.getElementById('deleteDocumentBtn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                const t = translations[getCurrentLang()] || translations['ru'];
                if (confirm(t.deleteConfirm)) {
                    document.getElementById('deleteForm').submit();
                }
            });
        }

        // ИИ ГЕНЕРАТОР
        const generateBtn = document.getElementById('generateBtn');
        const aiPrompt = document.getElementById('aiPrompt');
        const aiStatus = document.getElementById('aiStatus');
        const aiQuestions = document.getElementById('aiQuestions');
        const questionsList = document.getElementById('questionsList');
        const submitAnswers = document.getElementById('submitAnswers');
        const aiResult = document.getElementById('aiResult');
        const downloadLink = document.getElementById('downloadLink');
        const aiError = document.getElementById('aiError');
        let currentSessionId = null;

        async function sendAIRequest(payload) {
            const csrfToken = getCsrfToken();
            if (!csrfToken) throw new Error('CSRF токен не найден');
            const aiRoute = '/contracts/generate-document'; 
            
            const response = await fetch(aiRoute, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ ...payload, document_id: DOCUMENT_ID, action: 'edit' })
            });
            
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || `Ошибка сервера: ${response.status}`);
            }
            return await response.json();
        }

        function fillFormFields(data) {
            if (!data) return;
            const fields = { 'field-number': data.number, 'field-type': data.type, 'field-title': data.title, 'field-content': data.content, 'field-deadline': data.deadline, 'field-status': data.status };
            Object.keys(fields).forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field && fields[fieldId]) {
                    field.value = fields[fieldId];
                    field.style.borderColor = 'rgba(34,197,94,0.5)';
                    field.style.boxShadow = '0 0 0 2px rgba(34,197,94,0.15)';
                    setTimeout(() => { field.style.borderColor = ''; field.style.boxShadow = ''; }, 3000);
                }
            });
            saveFormToStorage();
        }

        function showQuestions(questions) {
            questionsList.innerHTML = '';
            questions.forEach((q, index) => {
                const item = document.createElement('div');
                item.className = 'question-item';
                item.innerHTML = `<div class="question-text">${q}</div><input type="text" class="question-input" placeholder="Ваш ответ..." data-idx="${index}">`;
                questionsList.appendChild(item);
            });
            aiQuestions.classList.remove('hidden');
        }

        function showError(message) {
            if (aiError) { aiError.textContent = message; aiError.classList.remove('hidden'); setTimeout(() => aiError.classList.add('hidden'), 5000); }
        }

        if (generateBtn) {
            generateBtn.addEventListener('click', async function() {
                const prompt = aiPrompt.value.trim();
                if (!prompt) { showError('Введите описание изменений'); return; }
                const format = document.querySelector('input[name="ai_format"]:checked').value;
                aiResult.classList.add('hidden'); aiQuestions.classList.add('hidden'); aiError.classList.add('hidden');
                aiStatus.classList.remove('hidden'); generateBtn.disabled = true;
                try {
                    const data = await sendAIRequest({ prompt, format, session_id: currentSessionId });
                    aiStatus.classList.add('hidden'); generateBtn.disabled = false;
                    if (data.status === 'success') {
                        if (data.needs_questions) { currentSessionId = data.session_id; showQuestions(data.questions); }
                        else { fillFormFields(data.document_data); if (data.download_url) { downloadLink.href = data.download_url; } aiResult.classList.remove('hidden'); }
                    } else { showError(data.message || 'Ошибка генерации'); }
                } catch (error) { aiStatus.classList.add('hidden'); generateBtn.disabled = false; showError('Ошибка: ' + error.message); }
            });
        }

        if (submitAnswers) {
            submitAnswers.addEventListener('click', async function() {
                const answers = {};
                document.querySelectorAll('.question-input').forEach((input) => { answers[`question_${input.dataset.idx}`] = input.value.trim(); });
                aiQuestions.classList.add('hidden'); aiStatus.classList.remove('hidden'); submitAnswers.disabled = true;
                try {
                    const data = await sendAIRequest({ session_id: currentSessionId, answers });
                    aiStatus.classList.add('hidden'); submitAnswers.disabled = false;
                    if (data.status === 'success') { fillFormFields(data.document_data); if (data.download_url) { downloadLink.href = data.download_url; } aiResult.classList.remove('hidden'); }
                    else { showError(data.message || 'Ошибка генерации'); }
                } catch (error) { aiStatus.classList.add('hidden'); submitAnswers.disabled = false; showError('Ошибка: ' + error.message); }
            });
        }
    });
</script>
@endsection