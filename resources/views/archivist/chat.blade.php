{{--
    ИИ-АРХИВИСТ (Поиск по всему архиву документов)
--}}
@extends('layouts.admin')

@section('content')

<style>
    .archivist-scope, .archivist-scope * { box-sizing: border-box; }
    html[data-theme="dark"] body{ background:#06070b !important; color:#e7ecf3 !important; }
    html[data-theme="light"] body{ background:#f4f6fa !important; color:#1e293b !important; }

    .arc-page { 
        display: flex; 
        gap: 16px; 
        width: 100%;
        max-width: 100%;
        margin: 0;
        padding: 0 20px;
        height: calc(100vh - 140px); 
        min-height: 520px; 
    }
    
    .arc-sidebar { 
        width: 320px; 
        flex-shrink: 0; 
        display: flex; 
        flex-direction: column; 
        gap: 12px; 
        min-height: 0;
        background: #ffffff;
        border-radius: 16px;
        padding: 16px;
        border: 1px solid #e2e8f0;
    }
    
    .new-arc-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 16px;
        min-height: 38px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.3px;
        color: #ffffff !important;
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.25);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .new-arc-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(139, 92, 246, 0.35);
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
    }
    
    .new-arc-btn:active {
        transform: translateY(0);
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.25);
    }
    
    .new-arc-btn i {
        font-size: 14px;
        font-weight: 600;
    }

    .sidebar-head { display: flex; align-items: center; justify-content: space-between; padding: 8px 4px 4px; }
    .sidebar-head span { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; color: #64748b; }
    .sidebar-count { 
        font-size: 11px; 
        font-weight: 700; 
        color: #7c3aed; 
        background: linear-gradient(135deg, #f3e8ff, #e9d5ff);
        border: 1px solid #ddd6fe;
        padding: 3px 10px; 
        border-radius: 20px; 
        min-width: 32px; 
        text-align: center;
        box-shadow: 0 2px 4px rgba(139, 92, 246, 0.1);
    }
    
    .arc-list { 
        flex: 1; 
        min-height: 0; 
        overflow-y: auto; 
        display: flex; 
        flex-direction: column; 
        gap: 6px; 
        padding: 8px 4px; 
        scrollbar-width: thin; 
        scrollbar-color: rgba(139, 92, 246, 0.3) transparent; 
    }
    .arc-list::-webkit-scrollbar { width: 5px; }
    .arc-list::-webkit-scrollbar-thumb { background: rgba(139, 92, 246, 0.3); border-radius: 10px; }
    .arc-list::-webkit-scrollbar-thumb:hover { background: rgba(139, 92, 246, 0.5); }
    
    .arc-item { 
        position: relative; 
        display: flex; 
        align-items: center; 
        gap: 8px; 
        border-radius: 10px; 
        border: 1px solid transparent; 
        transition: all 0.2s ease; 
        cursor: pointer;
        background: transparent;
    }
    .arc-item:hover { 
        background: rgba(139, 92, 246, 0.08);
        border-color: rgba(139, 92, 246, 0.15);
        transform: translateX(3px);
    }
    .arc-item-content { 
        flex: 1; 
        min-width: 0; 
        display: flex; 
        align-items: center; 
        gap: 10px; 
        padding: 10px 12px; 
        color: #475569; 
        font-size: 12.5px; 
        font-weight: 500; 
    }
    .arc-item:hover .arc-item-content { color: #7c3aed; }
    .arc-item-content i { font-size: 14px; flex-shrink: 0; opacity: 0.7; color: #94a3b8; }
    .arc-item-text { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .arc-item-del { 
        flex-shrink: 0; 
        width: 28px; 
        height: 28px; 
        margin-right: 4px; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        border-radius: 8px; 
        background: transparent; 
        color: #94a3b8; 
        font-size: 13px; 
        cursor: pointer; 
        opacity: 0; 
        transition: all 0.2s ease; 
        border: none; 
    }
    .arc-item:hover .arc-item-del { opacity: 1; }
    .arc-item-del:hover { color: #ef4444; background: rgba(239, 68, 68, 0.1); }

    .sidebar-empty { text-align: center; padding: 32px 16px; color: #94a3b8; font-size: 12px; line-height: 1.6; border: 2px dashed #e2e8f0; border-radius: 12px; background: rgba(248, 250, 252, 0.5); }
    .sidebar-empty i { display:block; font-size: 28px; margin-bottom: 10px; color:#cbd5e1; }
    .sidebar-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 1040; }
    .sidebar-backdrop.show { display:block; }

    .arc-main { 
        flex: 1; 
        min-width: 0; 
        display: flex; 
        flex-direction: column; 
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    
    .arc-header { 
        display: flex; 
        align-items: center; 
        gap: 14px; 
        padding: 16px 22px; 
        border-bottom: 1px solid #f1f5f9;
        background: #ffffff;
    }
    
    .arc-header-icon { 
        width: 42px; 
        height: 42px; 
        flex-shrink: 0; 
        border-radius: 12px; 
        background: linear-gradient(135deg, #f3e8ff, #e9d5ff); 
        border: 1px solid #ddd6fe; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        color: #7c3aed; 
        font-size: 18px; 
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.15); 
    }
    
    .arc-header-info { min-width: 0; flex: 1; }
    .arc-header-title { font-size: 15px; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #1e293b; }
    .arc-header-sub { font-size: 11px; color: #64748b; margin-top: 3px; }
    .arc-header-status { 
        display: inline-flex; 
        align-items: center; 
        gap: 6px; 
        font-size: 10px; 
        font-weight: 700; 
        color: #059669; 
        background: linear-gradient(135deg, #ecfdf5, #d1fae5); 
        border: 1px solid #a7f3d0; 
        padding: 5px 11px; 
        border-radius: 20px; 
        flex-shrink: 0; 
        box-shadow: 0 2px 6px rgba(5, 150, 105, 0.1); 
    }
    
    .status-dot { 
        width:7px;
        height:7px;
        border-radius:50%;
        background:#10b981;
        box-shadow:0 0 10px #10b981; 
        animation: pulse 2s infinite; 
    }
    
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
    
    .burger { 
        display: none; 
        width: 40px; 
        height: 40px; 
        flex-shrink: 0; 
        border-radius: 10px; 
        border: 1px solid #e2e8f0; 
        background: #ffffff; 
        color: #64748b; 
        font-size: 17px; 
        cursor: pointer; 
        box-shadow: 0 2px 6px rgba(0,0,0,0.05); 
        transition: all 0.2s; 
    }
    
    .burger:hover { 
        background: #f8fafc; 
        color: #7c3aed; 
        border-color: #ddd6fe; 
    }

    .arc-messages { 
        flex: 1; 
        min-height: 0; 
        overflow-y: auto; 
        padding: 24px; 
        display: flex; 
        flex-direction: column; 
        gap: 16px; 
        scroll-behavior: smooth; 
        scrollbar-width: thin; 
        scrollbar-color: rgba(139, 92, 246, 0.3) transparent;
        background: #f8fafc;
    }
    
    .arc-messages::-webkit-scrollbar { width: 6px; }
    .arc-messages::-webkit-scrollbar-thumb { background: rgba(139, 92, 246, 0.3); border-radius: 10px; }
    .arc-messages::-webkit-scrollbar-thumb:hover { background: rgba(139, 92, 246, 0.5); }
    
.msg { 
    max-width: 50%;
    padding: 15px 18px; 
    border-radius: 16px; 
    font-size: 13.5px; 
    line-height: 1.7; 
    animation: msgIn 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
    box-shadow: 0 4px 12px rgba(0,0,0,0.06); 
}
    
.msg.user { 
    align-self: flex-end;
    background: linear-gradient(135deg, #8b5cf6, #7c3aed); 
    color: #ffffff; 
    font-weight: 600; 
    border-bottom-right-radius: 6px; 
    box-shadow: 0 6px 16px rgba(139, 92, 246, 0.25); 
}
    @keyframes msgIn { from { opacity:0; transform: translateY(12px) scale(0.97);} to {opacity:1; transform:none;} }
    
    .msg-body { white-space: pre-wrap; word-break: break-word; }
    .msg-body p { margin: 0 0 9px 0; }
    .msg-body p:last-child { margin-bottom: 0; }

.msg.bot { 
    align-self: flex-start;
    background: #ffffff; 
    border: 1px solid #e2e8f0; 
    color: #1e293b; 
    border-bottom-left-radius: 6px; 
}

.msg.error { 
    align-self: flex-start;
    background: linear-gradient(135deg, #fef2f2, #fee2e2); 
    border-color: #fecaca; 
    color: #dc2626; 
}

.msg.error { 
    background: linear-gradient(135deg, #fef2f2, #fee2e2); 
    border-color: #fecaca; 
    color: #dc2626; 
}
    .msg-time { display: block; margin-top: 7px; font-size: 10px; opacity: 0.6; font-weight: 500; }

    .doc-cards { 
        display: flex; 
        flex-direction: column; 
        gap: 10px; 
        margin-top: 16px; 
        padding-top: 14px; 
        border-top: 2px dashed #e2e8f0; 
    }
    
    .doc-card { 
        display: flex; 
        align-items: flex-start; 
        gap: 13px; 
        padding: 13px 16px; 
        border-radius: 13px; 
        background: #ffffff; 
        border: 1px solid #e2e8f0; 
        text-decoration: none; 
        color: #1e293b; 
        font-size: 13px; 
        transition: all 0.25s ease; 
        box-shadow: 0 3px 10px rgba(0,0,0,0.04); 
    }
    
    .doc-card:hover { 
        background: #f8fafc; 
        border-color: #c4b5fd; 
        transform: translateY(-2px); 
        box-shadow: 0 8px 20px rgba(139, 92, 246, 0.12); 
        color: #1e293b; 
    }
    
    .doc-card-ico { 
        width: 38px; 
        height: 38px; 
        flex-shrink: 0; 
        border-radius: 10px; 
        background: linear-gradient(135deg, #f3e8ff, #e9d5ff); 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        color: #7c3aed; 
        font-size: 17px; 
        box-shadow: 0 3px 8px rgba(139, 92, 246, 0.15); 
    }
    
    .doc-card-ico.fmt-pdf { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #dc2626; }
    .doc-card-ico.fmt-word { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #2563eb; }
    .doc-card-ico.fmt-excel { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #059669; }
    
    .doc-card-info { flex: 1; min-width: 0; }
    .doc-card-title { font-weight: 700; font-size: 13.5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .doc-card-meta { font-size: 11px; color: #64748b; margin-top: 3px; display: flex; gap: 9px; flex-wrap: wrap; align-items: center; }
    .doc-card-meta .fmt-badge { font-weight: 700; font-size: 10px; padding: 2px 7px; border-radius: 6px; background: linear-gradient(135deg, #f1f5f9, #e2e8f0); border: 1px solid #cbd5e1; letter-spacing: 0.5px; }
    .doc-card-deadline { margin-top: 6px; font-size: 11px; font-weight: 700; color: #64748b; }
    .doc-card-deadline.dl-warn { color: #d97706; }
    .doc-card-deadline.dl-bad { color: #dc2626; }
    .doc-card-people { margin-top: 4px; font-size: 11px; color: #64748b; }
    .doc-card-quote { margin-top: 8px; padding-top: 8px; border-top: 1px dashed #e2e8f0; font-size: 11.5px; color: #64748b; font-style: italic; line-height: 1.6; }
    .doc-card-status { font-size: 10px; font-weight: 700; padding: 4px 9px; border-radius: 20px; background: linear-gradient(135deg, #ecfdf5, #d1fae5); color: #059669; border: 1px solid #a7f3d0; flex-shrink: 0; }
    .doc-card-status.pending { background: linear-gradient(135deg, #fffbeb, #fef3c7); color: #d97706; border-color: #fde68a; }
    .doc-card-status.rejected { background: linear-gradient(135deg, #fef2f2, #fee2e2); color: #dc2626; border-color: #fecaca; }
    
    .meta-line { margin-top: 12px; padding-top: 12px; border-top: 2px dashed #f1f5f9; font-size: 11px; color: #64748b; display: flex; gap: 16px; flex-wrap: wrap; }
    .meta-line span { display: inline-flex; align-items: center; gap: 6px; }
    .meta-line i { color: #8b5cf6; }

    .typing { display:flex; gap:6px; align-items:center; padding:16px 19px; }
    .typing span { width:8px; height:8px; border-radius:50%; background: linear-gradient(135deg, #8b5cf6, #7c3aed); animation: bounce 1.3s infinite; box-shadow: 0 2px 6px rgba(139, 92, 246, 0.3); }
    .typing span:nth-child(2){ animation-delay:.15s } .typing span:nth-child(3){ animation-delay:.3s }
    @keyframes bounce { 0%,60%,100%{transform:translateY(0);opacity:0.4} 30%{transform:translateY(-6px);opacity:1} }

    .welcome { text-align:center; margin:auto; max-width:620px; padding:24px; animation: msgIn 0.4s ease; }
    .welcome-icon { width:72px; height:72px; margin:0 auto 20px; border-radius:20px; background: linear-gradient(135deg, #f3e8ff, #e9d5ff); border: 2px solid #ddd6fe; display:flex; align-items:center; justify-content:center; font-size:32px; color:#7c3aed; box-shadow:0 8px 24px rgba(139, 92, 246, 0.2); }
    .welcome h2 { font-size:21px; font-weight:800; margin-bottom:10px; color: #1e293b; }
    .welcome p { font-size:13.5px; color:#64748b; line-height:1.7; margin-bottom:22px; }
    .welcome-chips { display:flex; flex-direction:column; gap:10px; }
    .welcome-chip { width:100%; padding:13px 16px; border-radius:12px; text-align:left; background: #ffffff; border:1px solid #e2e8f0; font:inherit; font-size:12.5px; color:#475569; cursor:pointer; transition:all 0.25s ease; box-shadow: 0 2px 6px rgba(0,0,0,0.04); }
    .welcome-chip:hover { border-color:#c4b5fd; color:#7c3aed; background: #f8fafc; transform:translateX(4px); box-shadow: 0 4px 12px rgba(139, 92, 246, 0.12); }

    .arc-input-area { 
        display:flex; 
        gap:12px; 
        align-items:flex-end; 
        padding:16px 20px; 
        border-top:1px solid #f1f5f9;
        background: #ffffff;
    }
    
    #arcInput { 
        flex:1; 
        min-width:0; 
        background:#ffffff; 
        border:2px solid #e2e8f0; 
        border-radius:14px; 
        padding:14px 17px; 
        color:#1e293b; 
        font:500 13.5px 'Inter', system-ui, sans-serif; 
        outline:none; 
        resize:none; 
        height:50px; 
        max-height:150px; 
        line-height:1.5; 
        transition:all 0.25s; 
        scrollbar-width: thin; 
        box-shadow: 0 2px 6px rgba(0,0,0,0.03); 
    }
    
    #arcInput::placeholder { color:#94a3b8; }
    #arcInput:focus { 
        border-color:#c4b5fd; 
        background:#ffffff; 
        box-shadow:0 0 0 4px rgba(139, 92, 246, 0.12), 0 4px 12px rgba(0,0,0,0.05); 
    }
    
    #sendBtn { 
        width:50px; 
        height:50px; 
        flex-shrink:0; 
        border:none; 
        border-radius:14px; 
        background: linear-gradient(135deg, #8b5cf6, #7c3aed); 
        color:#ffffff; 
        font-size:18px; 
        cursor:pointer; 
        display:flex; 
        align-items:center; 
        justify-content:center; 
        transition:all 0.25s ease; 
        box-shadow:0 6px 16px rgba(139, 92, 246, 0.3); 
    }
    
    #sendBtn:hover:not(:disabled){ 
        transform:translateY(-2px); 
        box-shadow:0 10px 24px rgba(139, 92, 246, 0.4); 
    }
    
    #sendBtn:active { transform:translateY(0); }
    #sendBtn:disabled { opacity:0.5; cursor:not-allowed; box-shadow:none; }
    
    .input-hint { font-size:11px; color:#94a3b8; padding:0 20px 12px; background: #ffffff; }

    .toast-wrap { position:fixed; top:20px; right:20px; z-index:1300; display:flex; flex-direction:column; gap:10px; pointer-events:none; }
    .toast { display:flex; align-items:center; gap:11px; min-width:260px; max-width:360px; padding:14px 17px; border-radius:13px; background: #ffffff; border:1px solid #e2e8f0; box-shadow:0 12px 32px rgba(0,0,0,0.12); font-size:13px; font-weight:600; color:#1e293b; animation: toastIn 0.3s cubic-bezier(0.4, 0, 0.2, 1); pointer-events:auto; }
    .toast.hide { animation: toastOut 0.25s ease forwards; }
    .toast i { font-size:17px; }
    .toast.ok { border-color:#a7f3d0; } .toast.ok i { color:#059669; }
    .toast.err { border-color:#fecaca; } .toast.err i { color:#dc2626; }
    @keyframes toastIn { from{opacity:0; transform:translateX(30px) scale(0.95)} to{opacity:1; transform:none} }
    @keyframes toastOut { to{opacity:0; transform:translateX(30px) scale(0.95)} }

    @media (max-width: 900px) {
        .arc-page { 
            height: calc(100vh - 118px); 
            gap: 0;
            padding: 0;
        }
        .burger { display:flex; align-items:center; justify-content:center; }
        .arc-main { border-radius:16px; }
        .msg { max-width: 100%; }
        .arc-sidebar { 
            position: fixed; 
            top: 0; 
            left: 0; 
            bottom: 0; 
            width: 300px; 
            z-index: 1050; 
            padding: 18px 16px; 
            background: #ffffff; 
            border-right: 1px solid #e2e8f0; 
            transform: translateX(-105%); 
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            box-shadow: 20px 0 60px rgba(0,0,0,0.12); 
        }
        .arc-sidebar.open { transform: none; }
        .arc-item-del { opacity:1; }
        .toast-wrap { top:12px; right:12px; left:12px; }
        .toast { min-width:0; max-width:none; }
    }
    
    @media (max-width: 420px) {
        .arc-header-status { display:none; }
        .arc-messages { padding:18px 14px; }
        .arc-page { padding: 0 10px; }
    }

    /* Dark theme */
    html[data-theme="dark"] .arc-sidebar { 
        background: #111827;
        border-color: #334155;
    }
    html[data-theme="dark"] .sidebar-head span { color: #a5b4fc; }
    html[data-theme="dark"] .arc-item-content { color: #cbd5e1; }
    html[data-theme="dark"] .arc-item:hover .arc-item-content { color: #c4b5fd; }
    html[data-theme="dark"] .sidebar-empty { 
        color: #94a3b8; 
        background: #1e293b;
        border-color: #334155;
    }
    html[data-theme="dark"] .sidebar-empty i { color: #475569; }
    html[data-theme="dark"] .arc-main { 
        background: #111827;
        border-color: #334155;
    }
    html[data-theme="dark"] .arc-header { 
        background: #111827;
        border-bottom-color: #334155;
    }
    html[data-theme="dark"] .arc-header-title { color: #f1f5f9; }
    html[data-theme="dark"] .arc-header-sub { color: #94a3b8; }
    html[data-theme="dark"] .arc-messages { 
        background: #0b1220;
    }
    html[data-theme="dark"] .msg.bot { 
        background: #172033;
        border-color: #334155;
        color: #e2e8f0;
    }
    html[data-theme="dark"] .doc-card { 
        background: #172033;
        border-color: #334155;
        color: #e2e8f0;
    }
    html[data-theme="dark"] .doc-card:hover { 
        background: #1e293b;
        border-color: #a78bfa;
    }
    html[data-theme="dark"] .doc-card-title { color: #f1f5f9; }
    html[data-theme="dark"] .doc-card-meta,
    html[data-theme="dark"] .doc-card-people,
    html[data-theme="dark"] .doc-card-deadline,
    html[data-theme="dark"] .doc-card-quote,
    html[data-theme="dark"] .meta-line { color: #94a3b8; }
    html[data-theme="dark"] .doc-card-quote,
    html[data-theme="dark"] .doc-cards,
    html[data-theme="dark"] .meta-line { border-color: #334155; }
    html[data-theme="dark"] .doc-card-meta .fmt-badge { 
        background: #1e293b;
        border-color: #475569;
        color: #cbd5e1;
    }
    html[data-theme="dark"] .welcome-chip { 
        background: #172033;
        border-color: #334155;
        color: #cbd5e1;
    }
    html[data-theme="dark"] .welcome-chip:hover { 
        background: #1e293b;
        border-color: #a78bfa;
        color: #c4b5fd;
    }
    html[data-theme="dark"] .arc-input-area { 
        background: #111827;
        border-top-color: #334155;
    }
    html[data-theme="dark"] #arcInput { 
        background: #172033;
        border-color: #475569;
        color: #f1f5f9;
    }
    html[data-theme="dark"] #arcInput::placeholder { color: #64748b; }
    html[data-theme="dark"] .input-hint { 
        background: #111827;
        color: #64748b;
    }
    html[data-theme="dark"] .toast { 
        background: #172033;
        border-color: #334155;
        color: #e2e8f0;
    }
    @media (max-width: 900px) { 
        html[data-theme="dark"] .arc-sidebar { 
            background: #111827;
            border-color: #334155;
        } 
    }
</style>
<div class="arc-page archivist-scope">
    <aside class="arc-sidebar" id="arcSidebar">
        <button class="new-arc-btn" id="newArcBtn"><i class="bi bi-plus-lg"></i> <span data-i18n="new_search">Новый поиск</span></button>
        <div class="sidebar-head"><span data-i18n="history">История</span><span class="sidebar-count" id="arcCount">0</span></div>
        <div class="arc-list" id="arcList"></div>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="arc-main">
        <div class="arc-header">
            <button type="button" class="burger" id="burgerBtn" aria-label="Меню"><i class="bi bi-list"></i></button>
            <div class="arc-header-icon"><i class="bi bi-archive-fill"></i></div>
            <div class="arc-header-info">
                <div class="arc-header-title" data-i18n="title">ИИ-Архивист</div>
                <div class="arc-header-sub" data-i18n="sub">Дедлайны, форматы, подписи, содержимое файлов</div>
            </div>
            <div class="arc-header-status"><span class="status-dot"></span> ONLINE</div>
        </div>

        <div class="arc-messages" id="arcMessages">
            <div class="welcome" id="welcomeBlock">
                <div class="welcome-icon"><i class="bi bi-archive-fill"></i></div>
                <h2 data-i18n="welcome_title">Архив в вашем распоряжении</h2>
                <p data-i18n="welcome_sub">Ищу по типу, сроку, участникам и содержимому файлов — задавайте вопрос своими словами.</p>
                <div class="welcome-chips">
                    <button type="button" class="welcome-chip" onclick="useHint(this)" data-i18n="hint_sign_today">✍️ Какие документы я должен подписать сегодня?</button>
                    <button type="button" class="welcome-chip" onclick="useHint(this)" data-i18n="hint_sign_week">⏰ Какие документы нужно подписать в течение 7 дней?</button>
                    <button type="button" class="welcome-chip" onclick="useHint(this)" data-i18n="hint_pdf_count">📊 Сколько у меня документов в формате PDF?</button>
                    <button type="button" class="welcome-chip" onclick="useHint(this)" data-i18n="hint_overdue">🔴 Какие документы просрочены?</button>
                    <button type="button" class="welcome-chip" onclick="useHint(this)" data-i18n="hint_content">🔎 Найди договоры, где говорится о расторжении</button>
                    <button type="button" class="welcome-chip" onclick="useHint(this)" data-i18n="hint_amount">💰 В каких документах указана сумма 50 000?</button>
                </div>
            </div>
        </div>

        <form class="arc-input-area" id="arcForm">
            <textarea id="arcInput" placeholder="Напиши вопрос по архиву..." data-i18n-ph="placeholder" rows="1"></textarea>
            <button type="submit" id="sendBtn"><i class="bi bi-send-fill"></i></button>
        </form>
        <div class="input-hint">Enter — <span data-i18n="send">отправить</span> · Shift + Enter — <span data-i18n="newline">новая строка</span></div>
    </div>
</div>
<div class="toast-wrap" id="toastWrap"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const TRANSLATIONS = {
        tj: { new_search: 'Ҷустуҷӯи нав', history: 'Таърих', title: 'Архивчии Зеҳнӣ', sub: 'Мӯҳлатҳо, форматҳо, имзоҳо, мундариҷаи файлҳо', welcome_title: 'Архив дар ихтиёри шумо', welcome_sub: 'Аз рӯи навъ, муҳлат, иштирокчиён ва матни файлҳо меҷӯям.', hint_sign_today: '✍️ Кадом ҳуҷҷатҳоро имрӯз бояд имзо кунам?', hint_sign_week: '⏰ Дар 7 рӯзи оянда кадом ҳуҷҷатҳоро бояд имзо кунам?', hint_pdf_count: '📊 Чанд ҳуҷҷати PDF дорам?', hint_overdue: '🔴 Кадом ҳуҷҷатҳо муҳлати гузашта доранд?', hint_content: '🔎 Шартномаҳоеро ёб, ки дар бораи бекоркунӣ мегӯянд', hint_amount: '💰 Дар кадом ҳуҷҷатҳо маблағи 50 000 навишта шудааст?', signed: 'имзо шудааст', waiting_signature: 'интизори имзо', rejected: 'рад шудааст', draft: 'лоиҳа', placeholder: 'Саволи худро нависед...', send: 'фиристодан', newline: 'сатри нав', found: 'Ёфт шуд', intent: 'намуди дархост' },
        ru: { new_search: 'Новый поиск', history: 'История', title: 'ИИ-Архивист', sub: 'Дедлайны, форматы, подписи, содержимое файлов', welcome_title: 'Архив в вашем распоряжении', welcome_sub: 'Ищу по типу, сроку, участникам и содержимому файлов.', hint_sign_today: '✍️ Какие документы я должен подписать сегодня?', hint_sign_week: '⏰ Какие документы нужно подписать в течение 7 дней?', hint_pdf_count: '📊 Сколько у меня документов в формате PDF?', hint_overdue: '🔴 Какие документы просрочены?', hint_content: '🔎 Найди договоры, где говорится о расторжении', hint_amount: '💰 В каких документах указана сумма 50 000?', signed: 'подписан', waiting_signature: 'ждёт подписи', rejected: 'отклонён', draft: 'черновик', placeholder: 'Напиши вопрос по архиву...', send: 'отправить', newline: 'новая строка', found: 'Найдено', intent: 'тип запроса' },
        en: { new_search: 'New search', history: 'History', title: 'AI Archivist', sub: 'Deadlines, formats, signatures, file content', welcome_title: 'Archive at your service', welcome_sub: 'Search by type, deadline, people, or file contents.', hint_sign_today: '✍️ Which documents do I need to sign today?', hint_sign_week: '⏰ Which documents need my signature in the next 7 days?', hint_pdf_count: '📊 How many PDF documents do I have?', hint_overdue: '🔴 Which documents are overdue?', hint_content: '🔎 Find contracts that mention termination', hint_amount: '💰 Which documents mention the amount 50,000?', signed: 'signed', waiting_signature: 'awaiting signature', rejected: 'rejected', draft: 'draft', placeholder: 'Ask about archive...', send: 'send', newline: 'new line', found: 'Found', intent: 'query type' }
    };
    let LANG = localStorage.getItem('docsign_lang') || 'ru';
    const t = (k, fallback) => (TRANSLATIONS[LANG] && TRANSLATIONS[LANG][k]) || fallback || k;

    function applyTranslations(lang) {
        LANG = TRANSLATIONS[lang] ? lang : 'ru';
        document.querySelectorAll('[data-i18n]').forEach(el => { if (TRANSLATIONS[LANG][el.getAttribute('data-i18n')] !== undefined) el.innerHTML = TRANSLATIONS[LANG][el.getAttribute('data-i18n')]; });
        document.querySelectorAll('[data-i18n-ph]').forEach(el => { if (TRANSLATIONS[LANG][el.getAttribute('data-i18n-ph')] !== undefined) el.placeholder = TRANSLATIONS[LANG][el.getAttribute('data-i18n-ph')]; });
    }
    applyTranslations(LANG);
    window.addEventListener('docsign:lang-changed', e => applyTranslations(e.detail?.lang || 'ru'));

    const toastWrap = document.getElementById('toastWrap');
    function toast(message, type = 'info') {
        const icons = { ok: 'bi-check-circle-fill', err: 'bi-x-circle-fill', info: 'bi-info-circle-fill' };
        const el = document.createElement('div');
        el.className = 'toast ' + type;
        el.innerHTML = '<i class="bi ' + (icons[type] || icons.info) + '"></i><span></span>';
        el.querySelector('span').textContent = message;
        toastWrap.appendChild(el);
        setTimeout(() => { el.classList.add('hide'); setTimeout(() => el.remove(), 260); }, 3200);
    }

    const sidebar = document.getElementById('arcSidebar'), backdrop = document.getElementById('sidebarBackdrop'), burger = document.getElementById('burgerBtn');
    function openSidebar() { sidebar.classList.add('open'); backdrop.classList.add('show'); }
    function closeSidebar() { sidebar.classList.remove('open'); backdrop.classList.remove('show'); }
    burger?.addEventListener('click', openSidebar);
    backdrop?.addEventListener('click', closeSidebar);

    const HISTORY_KEY = 'archivist_history_v1', arcList = document.getElementById('arcList'), arcCount = document.getElementById('arcCount');
    function loadHistory() { try { return JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]'); } catch (e) { return []; } }
    function saveHistory(items) { localStorage.setItem(HISTORY_KEY, JSON.stringify(items.slice(0, 50))); }
    function renderHistory() {
        const items = loadHistory(); arcList.innerHTML = ''; arcCount.textContent = items.length;
        if (items.length === 0) { arcList.innerHTML = '<div class="sidebar-empty"><i class="bi bi-inbox"></i><span>' + t('history') + ': —</span></div>'; return; }
        items.forEach((item, idx) => {
            const row = document.createElement('div'); row.className = 'arc-item';
            row.innerHTML = '<div class="arc-item-content"><i class="bi bi-search"></i><span class="arc-item-text"></span></div><button type="button" class="arc-item-del" aria-label="Удалить"><i class="bi bi-x-lg"></i></button>';
            row.querySelector('.arc-item-text').textContent = item.q;
            row.querySelector('.arc-item-content').addEventListener('click', () => { document.getElementById('arcInput').value = item.q; send(); closeSidebar(); });
            row.querySelector('.arc-item-del').addEventListener('click', (e) => { e.stopPropagation(); const cur = loadHistory(); cur.splice(idx, 1); saveHistory(cur); renderHistory(); });
            arcList.appendChild(row);
        });
    }

    const sendUrl = @json(route('archivist.chat')), box = document.getElementById('arcMessages'), input = document.getElementById('arcInput'), sendBtn = document.getElementById('sendBtn');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    let busy = false;
    const WELCOME_CHIPS = ['hint_sign_today', 'hint_sign_week', 'hint_pdf_count', 'hint_overdue', 'hint_content', 'hint_amount'];

    function welcomeHtml() {
        return '<div class="welcome-icon"><i class="bi bi-archive-fill"></i></div><h2 data-i18n="welcome_title">' + t('welcome_title') + '</h2><p data-i18n="welcome_sub">' + t('welcome_sub') + '</p><div class="welcome-chips">' + WELCOME_CHIPS.map(k => '<button type="button" class="welcome-chip" onclick="useHint(this)">' + t(k) + '</button>').join('') + '</div>';
    }

    window.useHint = function (el) { input.value = el.textContent.replace(/^[^\s]+\s/, ''); autoGrow(); input.focus(); closeSidebar(); };
    document.getElementById('newArcBtn').addEventListener('click', () => { box.innerHTML = ''; const w = document.createElement('div'); w.className = 'welcome'; w.id = 'welcomeBlock'; w.innerHTML = welcomeHtml(); box.appendChild(w); applyTranslations(LANG); input.value = ''; input.focus(); });

    function autoGrow() { input.style.height = '48px'; input.style.height = Math.min(input.scrollHeight, 140) + 'px'; }
    input.addEventListener('input', autoGrow);
    function scrollDown() { box.scrollTop = box.scrollHeight; }
    function nowTime() { const d = new Date(); return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0'); }
    function escapeHtml(str) { const div = document.createElement('div'); div.textContent = str == null ? '' : String(str); return div.innerHTML; }

    function statusClass(s) {
        const v = (s || '').toLowerCase();
        if (v === 'rejected' || v.includes('reject') || v.includes('fail') || v.includes('отклон')) return 'rejected';
        if (v === 'draft' || v.includes('active') || v.includes('processing') || v.includes('waiting') || v.includes('ожид') || v.includes('чернов')) return 'pending';
        return '';
    }

    function formatInfo(ext) {
        const e = (ext || '').toLowerCase();
        if (e === 'pdf') return { icon: 'bi-file-earmark-pdf-fill', cls: 'fmt-pdf' };
        if (e === 'doc' || e === 'docx') return { icon: 'bi-file-earmark-word-fill', cls: 'fmt-word' };
        if (e === 'xls' || e === 'xlsx') return { icon: 'bi-file-earmark-excel-fill', cls: 'fmt-excel' };
        return { icon: 'bi-file-earmark-text-fill', cls: '' };
    }

    function addMsg(role, text, docs, meta) {
        const welcome = document.getElementById('welcomeBlock'); if (welcome) welcome.remove();
        const div = document.createElement('div'); div.className = 'msg ' + role;
        const body = document.createElement('div'); body.className = 'msg-body'; body.innerHTML = escapeHtml(text).replace(/\n/g, '<br>'); div.appendChild(body);

        if (docs && docs.length > 0) {
            const cards = document.createElement('div'); cards.className = 'doc-cards';
            docs.forEach(d => {
                const fmt = formatInfo(d.format);
                const card = document.createElement('a'); card.className = 'doc-card'; card.href = d.url || ('/documents/' + d.id); card.target = '_blank';
                let deadlineHtml = '';
                if (d.deadline) {
                    let cls = '', label = '⏰ ' + d.deadline;
                    if (d.days_left !== null && d.days_left !== undefined) {
                        if (d.days_left < 0) { cls = 'dl-bad'; label += ' · просрочен на ' + Math.abs(d.days_left) + ' дн.'; }
                        else if (d.days_left === 0) { cls = 'dl-warn'; label += ' · СЕГОДНЯ!'; }
                        else if (d.days_left <= 7) { cls = 'dl-warn'; label += ' · осталось ' + d.days_left + ' дн.'; }
                        else { label += ' · осталось ' + d.days_left + ' дн.'; }
                    }
                    deadlineHtml = '<div class="doc-card-deadline ' + cls + '">' + label + '</div>';
                }
                let peopleHtml = d.owner || d.receiver ? '<div class="doc-card-people">👤 ' + escapeHtml(d.owner || '?') + ' → ' + escapeHtml(d.receiver || '?') + '</div>' : '';
                let quoteHtml = d.quote ? '<div class="doc-card-quote">«' + escapeHtml(d.quote) + '»</div>' : '';
                card.innerHTML = '<div class="doc-card-ico ' + fmt.cls + '"><i class="bi ' + fmt.icon + '"></i></div><div class="doc-card-info"><div class="doc-card-title"></div><div class="doc-card-meta"><span>ID: #' + d.id + '</span>' + (d.format ? '<span class="fmt-badge">' + escapeHtml(d.format.toUpperCase()) + '</span>' : '') + '<span>' + escapeHtml(t(d.signature_state === 'signed' ? 'signed' : (d.signature_state === 'rejected' ? 'rejected' : (d.signature_state === 'draft' ? 'draft' : 'waiting_signature')))) + '</span></div>' + deadlineHtml + peopleHtml + quoteHtml + '</div><span class="doc-card-status ' + statusClass(d.signature_state) + '"></span>';
                card.querySelector('.doc-card-title').textContent = d.title || 'Документ';
                card.querySelector('.doc-card-status').textContent = t(d.signature_state || 'waiting_signature');
                cards.appendChild(card);
            });
            div.appendChild(cards);
        }

        if (meta && role === 'bot') {
            const ml = document.createElement('div'); ml.className = 'meta-line';
            ml.innerHTML = '<span><i class="bi bi-folder2-open"></i> ' + t('found') + ': <b>' + (meta.found_count || 0) + '</b></span><span><i class="bi bi-lightning-charge-fill"></i> ' + t('intent') + ': <b>' + escapeHtml(meta.intent || '—') + '</b></span>';
            div.appendChild(ml);
        }
        const tm = document.createElement('span'); tm.className = 'msg-time'; tm.textContent = nowTime(); div.appendChild(tm);
        box.appendChild(div); scrollDown(); return div;
    }

    function showTyping() { const el = document.createElement('div'); el.className = 'msg bot typing'; el.id = 'typingBox'; el.innerHTML = '<span></span><span></span><span></span>'; box.appendChild(el); scrollDown(); }
    function hideTyping() { document.getElementById('typingBox')?.remove(); }

    async function send() {
        const text = input.value.trim(); if (!text || busy) return;
        busy = true; sendBtn.disabled = true; closeSidebar();
        addMsg('user', text); input.value = ''; autoGrow(); showTyping();
        try {
            const resp = await fetch(sendUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ message: text }) });
            const contentType = resp.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) throw new Error('Сервер вернул ошибку. Проверьте логи.');
            const data = await resp.json(); hideTyping();
            if (!resp.ok) throw new Error(data.message || data.error || 'Не удалось выполнить поиск.');
            addMsg('bot', data.answer || '—', data.documents || [], data.meta || {});
            const hist = loadHistory(); if (!hist.some(h => h.q === text)) { hist.unshift({ q: text, t: Date.now() }); saveHistory(hist); renderHistory(); }
        } catch (e) { hideTyping(); addMsg('error', '❌ ' + e.message); }
        busy = false; sendBtn.disabled = false; input.focus();
    }

    document.getElementById('arcForm').addEventListener('submit', e => { e.preventDefault(); send(); });
    input.addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); } });
    renderHistory(); input.focus();
});
</script>
@endsection