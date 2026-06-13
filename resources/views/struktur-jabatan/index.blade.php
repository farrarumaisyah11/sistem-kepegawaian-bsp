@extends('layouts.app')

@section('title', 'Struktur Jabatan')

@section('content')
@php
    $isFullscreen = request()->boolean('fullscreen');

    $fullscreenParams = request()->query();
    $fullscreenParams['fullscreen'] = 1;

    $normalParams = request()->query();
    unset($normalParams['fullscreen']);

    $logoBsp = asset('images/logo-bsp.png');
    $logoSkk = asset('images/logo-skk-migas.png');
@endphp

<style>
    :root {
        --org-navy: #273957;
        --org-navy-2: #1f2d45;
        --org-olive: #6b775c;
        --org-olive-dark: #4f5c40;
        --org-olive-soft: #eef3e8;
        --org-olive-soft-2: #f7f9f3;
        --org-bg: #f7f9f5;
        --org-card: #ffffff;
        --org-border: #d8e0cf;
        --org-border-soft: #edf0ea;
        --org-text: #111827;
        --org-muted: #667085;
        --org-success: #178f52;
        --org-danger: #d92d20;
        --org-line: #9aa68e;
        --org-shadow: 0 14px 35px rgba(15,23,42,.07);
        --org-shadow-soft: 0 8px 20px rgba(15,23,42,.045);
    }

    footer,
    .footer,
    .main-footer,
    .app-footer {
        position: static !important;
        bottom: auto !important;
        top: auto !important;
        margin-top: 28px !important;
    }

    body.org-fullscreen-mode {
        background: var(--org-bg) !important;
    }

    body.org-fullscreen-mode .sidebar,
    body.org-fullscreen-mode .sidebar-page-rail,
    body.org-fullscreen-mode .topbar,
    body.org-fullscreen-mode .navbar,
    body.org-fullscreen-mode .main-header,
    body.org-fullscreen-mode .app-header,
    body.org-fullscreen-mode header {
        display: none !important;
    }

    body.org-fullscreen-mode .main-content,
    body.org-fullscreen-mode .page-content,
    body.org-fullscreen-mode .content-wrapper,
    body.org-fullscreen-mode .container,
    body.org-fullscreen-mode .container-fluid {
        margin-left: 0 !important;
        margin-right: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
    }

    .org-page {
        min-height: calc(100vh - 80px);
        padding: 24px 18px 42px;
        background: var(--org-bg);
        overflow-x: hidden;
        color: var(--org-text);
    }

    .org-page.is-fullscreen {
        min-height: 100vh;
        padding: 18px 18px 34px;
    }

    .org-shell {
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
    }

    .org-hero {
        background: linear-gradient(135deg, #6b775c 0%, #4f5c40 100%);
        border: 1px solid rgba(255,255,255,.14);
        border-radius: 22px;
        padding: 24px 26px;
        color: #ffffff;
        box-shadow: var(--org-shadow);
        margin-bottom: 16px;
        overflow: hidden;
    }

    .org-hero::before,
    .org-hero::after {
        content: none !important;
        display: none !important;
    }

    .org-hero-inner {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        flex-wrap: wrap;
    }

    .org-eyebrow {
        font-size: 12px;
        font-weight: 650;
        letter-spacing: 1.2px;
        color: rgba(255,255,255,.88);
        margin-bottom: 8px;
        text-transform: uppercase;
    }

    .org-title {
        font-size: clamp(24px, 2.4vw, 32px);
        font-weight: 680;
        margin: 0;
        letter-spacing: -.4px;
        line-height: 1.15;
        color: #ffffff;
    }

    .org-header-actions,
    .org-toolbar-actions,
    .org-filter-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .org-btn-light,
    .org-btn-primary,
    .org-btn-secondary,
    .org-mini-btn,
    .org-detail-link {
        border-radius: 12px;
        font-weight: 580;
        text-decoration: none;
        transition: .18s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        white-space: nowrap;
    }

    .org-btn-light {
        min-height: 40px;
        padding: 9px 14px;
        border: 1px solid rgba(255,255,255,.28);
        background: rgba(255,255,255,.13);
        color: #ffffff;
        backdrop-filter: blur(6px);
    }

    .org-btn-light:hover {
        background: rgba(255,255,255,.23);
        color: #ffffff;
        transform: translateY(-1px);
    }

    .org-btn-primary,
    .org-btn-secondary {
        min-height: 42px;
        padding: 9px 16px;
        border: 1px solid transparent;
    }

    .org-btn-primary {
        background: linear-gradient(135deg, var(--org-olive), var(--org-olive-dark));
        color: #ffffff;
        box-shadow: 0 7px 16px rgba(107,119,92,.18);
    }

    .org-btn-primary:hover {
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 9px 20px rgba(107,119,92,.24);
    }

    .org-btn-secondary {
        background: #ffffff;
        color: var(--org-olive-dark);
        border-color: #dfe6d8;
    }

    .org-btn-secondary:hover {
        background: var(--org-olive-soft);
        color: var(--org-olive-dark);
        border-color: #cbd8c2;
        transform: translateY(-1px);
    }

    .org-normal-control-card {
        background: #ffffff;
        border: 1px solid rgba(216,224,207,.95);
        border-radius: 18px;
        box-shadow: 0 10px 24px rgba(15,23,42,.055);
        padding: 18px 20px;
        margin-bottom: 16px;
    }

    .org-normal-control-inner {
        display: grid;
        grid-template-columns: minmax(420px, 1fr) minmax(360px, 520px);
        gap: 22px;
        align-items: end;
    }

    .org-metric-strip {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .org-metric-item {
        min-height: 74px;
        padding: 14px 14px;
        border: 1px solid #edf0ea;
        border-radius: 15px;
        background: linear-gradient(180deg, #ffffff 0%, #fbfcf8 100%);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .org-metric-text {
        min-width: 0;
    }

    .org-metric-label {
        color: #536044;
        font-size: 11.5px;
        line-height: 1.2;
        font-weight: 650;
        margin-bottom: 7px;
        letter-spacing: .2px;
    }

    .org-metric-value {
        color: var(--org-navy);
        font-size: 25px;
        line-height: 1;
        font-weight: 680;
        letter-spacing: -.5px;
    }

    .org-metric-icon {
        width: 36px;
        height: 36px;
        min-width: 36px;
        border-radius: 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--org-olive-dark);
        background: #f7f9f3;
        border: 1px solid #dfe6d7;
        font-size: 16px;
    }

    .org-metric-item.vacant .org-metric-icon {
        color: #9a3412;
        background: #fff7ed;
        border-color: #fed7aa;
    }

    .org-metric-item.total .org-metric-icon {
        color: var(--org-navy);
        background: #f8fafc;
        border-color: #e5e7eb;
    }

    .org-control-filter-form {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 12px;
        align-items: end;
    }

    .org-filter-card,
    .org-fullscreen-filter {
        border: 1px solid rgba(216,224,207,.95) !important;
        border-radius: 18px !important;
        background: #ffffff !important;
        box-shadow: 0 10px 24px rgba(15,23,42,.055) !important;
        overflow: hidden;
        margin-bottom: 16px !important;
    }

    .org-filter-card .card-body,
    .org-fullscreen-filter {
        padding: 0 !important;
    }

    .org-filter-panel {
        display: grid;
        grid-template-columns: minmax(320px, 1fr) auto;
        align-items: end;
        gap: 22px;
        padding: 18px 22px;
    }

    .org-filter-field {
        min-width: 0;
    }

    .org-filter-field .form-label {
        color: var(--org-navy) !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        margin-bottom: 8px !important;
    }

    .org-filter-field .form-select {
        width: 100%;
        min-height: 46px !important;
        border-radius: 14px !important;
        border: 1px solid #dfe6d7 !important;
        background-color: #ffffff !important;
        color: #1f2937 !important;
        font-size: 14px !important;
        font-weight: 550 !important;
        padding-left: 15px !important;
        padding-right: 42px !important;
        box-shadow: none !important;
    }

    .org-filter-field .form-select:focus {
        border-color: var(--org-olive) !important;
        box-shadow: 0 0 0 .22rem rgba(107,119,92,.14) !important;
    }

    .org-filter-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: nowrap;
    }

    .org-filter-actions .org-btn-primary,
    .org-filter-actions .org-btn-secondary {
        min-height: 46px;
        border-radius: 14px;
        padding: 10px 18px;
        font-size: 13.5px;
        font-weight: 650;
    }

    .org-filter-actions .org-btn-primary {
        min-width: 132px;
    }

    .org-filter-actions .org-btn-secondary {
        min-width: 104px;
    }

    .org-fullscreen-filter .org-filter-actions {
        flex-wrap: wrap;
    }

    .org-board-card {
        background: #ffffff;
        border: 1px solid var(--org-border);
        border-radius: 20px;
        box-shadow: 0 14px 36px rgba(15,23,42,.065);
        overflow: hidden;
        width: 100%;
        max-width: 100%;
    }

    .org-page.is-fullscreen .org-board-card {
        min-height: calc(100vh - 36px);
        border-radius: 18px;
    }

    .org-board-toolbar {
        background: linear-gradient(180deg, #ffffff 0%, #fbfcf8 100%);
        border-bottom: 1px solid var(--org-border-soft);
        padding: 15px 17px;
        display: flex;
        justify-content: space-between;
        gap: 14px;
        align-items: center;
        flex-wrap: wrap;
    }

    .org-board-title-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 260px;
    }

    .org-board-icon {
        width: 40px;
        height: 40px;
        min-width: 40px;
        border-radius: 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--org-olive-dark);
        background: var(--org-olive-soft-2);
        border: 1px solid #dfe6d7;
        box-shadow: none;
    }

    .org-board-title {
        font-weight: 650;
        color: var(--org-navy);
        font-size: 16px;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: .25px;
        line-height: 1.2;
    }

    .org-mini-btn {
        min-height: 35px;
        border-radius: 999px;
        padding: 7px 12px;
        border: 1px solid #dfe5d8;
        background: #ffffff;
        color: var(--org-olive-dark);
        font-size: 12px;
    }

    .org-mini-btn:hover {
        background: var(--org-olive-soft);
        color: var(--org-olive-dark);
        border-color: #cbd8c2;
        transform: translateY(-1px);
    }

    .org-legend {
        display: flex;
        align-items: center;
        gap: 9px;
        flex-wrap: wrap;
        font-size: 11.5px;
        color: var(--org-muted);
        font-weight: 560;
        background: #ffffff;
        border: 1px solid var(--org-border-soft);
        border-radius: 999px;
        padding: 7px 10px;
    }

    .org-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .org-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        display: inline-block;
    }

    .org-dot-filled {
        background: var(--org-success);
    }

    .org-dot-vacant {
        background: var(--org-danger);
    }

    .org-chart-scroll {
        width: 100%;
        overflow: auto;
        background: #ffffff;
        padding: 44px 34px 54px;
        min-height: 650px;
        cursor: grab;
        position: relative;
    }

    .org-chart-scroll::before {
        content: 'Geser untuk melihat struktur yang lebih lebar';
        position: sticky;
        left: 0;
        top: 0;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255,255,255,.9);
        border: 1px solid var(--org-border-soft);
        color: #667085;
        font-size: 11px;
        font-weight: 560;
        border-radius: 999px;
        padding: 7px 11px;
        z-index: 5;
        margin-bottom: 12px;
        backdrop-filter: blur(6px);
    }

    .org-chart-scroll:active {
        cursor: grabbing;
    }

    .org-page.is-fullscreen .org-chart-scroll {
        min-height: calc(100vh - 190px);
    }

    .org-chart-canvas {
        min-width: max-content;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        transform-origin: top center;
        transition: transform .2s ease;
    }

    .org-tree {
        display: flex;
        justify-content: center;
        min-width: max-content;
        width: max-content;
        padding: 2px;
    }

    .org-tree,
    .org-tree ul,
    .org-tree li {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .org-tree ul {
        position: relative;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding-top: 46px;
    }

    .org-tree ul::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        width: 0;
        height: 46px;
        border-left: 2px solid var(--org-line);
        transform: translateX(-50%);
    }

    .org-tree li {
        position: relative;
        text-align: center;
        padding: 46px 15px 0;
    }

    .org-tree li::before,
    .org-tree li::after {
        content: '';
        position: absolute;
        top: 0;
        width: 50%;
        height: 46px;
        border-top: 2px solid var(--org-line);
    }

    .org-tree li::before {
        right: 50%;
    }

    .org-tree li::after {
        left: 50%;
        border-left: 2px solid var(--org-line);
    }

    .org-tree li:only-child::before,
    .org-tree li:only-child::after {
        display: none;
    }

    .org-tree li:only-child {
        padding-top: 0;
    }

    .org-tree li:first-child::before,
    .org-tree li:last-child::after {
        border: none;
    }

    .org-tree li:last-child::before {
        border-right: 2px solid var(--org-line);
        border-radius: 0 9px 0 0;
    }

    .org-tree li:first-child::after {
        border-radius: 9px 0 0 0;
    }

    .org-tree > ul {
        padding-top: 0;
    }

    .org-tree > ul::before {
        display: none;
    }

    .org-tree > ul > li {
        padding-top: 0;
    }

    .org-tree > ul > li::before,
    .org-tree > ul > li::after {
        display: none;
    }

    .org-node-wrap {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 2;
    }

    .org-node {
        width: 196px;
        min-height: 132px;
        background: #ffffff;
        border: 1.5px solid rgba(107,119,92,.58);
        border-radius: 17px;
        box-shadow: 0 9px 20px rgba(15,23,42,.06);
        overflow: hidden;
        display: inline-flex;
        flex-direction: column;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        position: relative;
        z-index: 2;
    }

    .org-node::before,
    .org-node::after {
        content: none !important;
        display: none !important;
    }

    .org-node:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 28px rgba(15,23,42,.11);
        border-color: var(--org-olive);
    }

    .org-node-topline {
        display: none !important;
    }

    .org-node-topbar,
    .org-node-status-row {
        min-height: 31px;
        padding: 7px 9px 6px;
        background: linear-gradient(135deg, #6b775c 0%, #4f5c40 100%);
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .org-node-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-height: 19px;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 9.2px;
        line-height: 1;
        font-weight: 600;
        letter-spacing: .15px;
        text-transform: uppercase;
        background: rgba(255,255,255,.16);
        color: #ffffff;
        border: 1px solid rgba(255,255,255,.24);
    }

    .org-node-status .org-status-dot,
    .org-node-status-row .org-status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
        background: #ffffff;
    }

    .org-node-status-row .org-status-text {
        color: #ffffff;
        font-size: 9.2px;
        line-height: 1;
        font-weight: 600;
        letter-spacing: .15px;
        text-transform: uppercase;
    }

    .org-node-status-row {
        gap: 5px;
    }

    .org-node-title {
        min-height: 52px;
        padding: 10px 11px 8px;
        color: var(--org-navy);
        font-size: 10.8px;
        font-weight: 620;
        line-height: 1.25;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        overflow-wrap: anywhere;
        letter-spacing: .1px;
        background: #ffffff;
    }

    .org-node-holder {
        min-height: 39px;
        padding: 7px 10px;
        border-top: 1px solid var(--org-border-soft);
        background: #fbfcf8;
        color: #344054;
        font-size: 9.6px;
        font-weight: 520;
        line-height: 1.28;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        overflow-wrap: anywhere;
    }

    .org-holder-list {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .org-more-holder {
        color: var(--org-muted);
        font-size: 8.7px;
    }

    .org-vacant-text {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #9a3412;
        font-size: 9px;
        font-weight: 600;
        letter-spacing: .12px;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 999px;
        padding: 4px 8px;
        text-transform: none;
    }

    .org-node-meta {
        display: grid;
        grid-template-columns: 1fr 38px;
        border-top: 1px solid var(--org-border-soft);
        min-height: 32px;
        margin-top: auto;
    }

    .org-node-dept {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 6px 8px;
        font-size: 8.3px;
        font-weight: 590;
        color: var(--org-olive-dark);
        background: var(--org-olive-soft-2);
        line-height: 1.18;
        overflow-wrap: anywhere;
        text-transform: uppercase;
        letter-spacing: .18px;
    }

    .org-node-count {
        display: flex;
        align-items: center;
        justify-content: center;
        border-left: 1px solid var(--org-border-soft);
        font-size: 10.3px;
        font-weight: 600;
        color: var(--org-navy);
        background: #ffffff;
    }

    .org-node-count small {
        display: block;
        font-size: 5.9px;
        font-weight: 580;
        color: var(--org-muted);
        text-transform: uppercase;
        line-height: 1;
        margin-top: 1px;
    }

    .org-detail-link {
        margin-top: 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 25px;
        padding: 5px 11px;
        border-radius: 999px;
        background: #ffffff;
        border: 1px solid #dbe4d3;
        color: var(--org-olive-dark);
        font-size: 10px;
        font-weight: 560;
        text-decoration: none;
        position: relative;
        z-index: 3;
        box-shadow: none;
    }

    .org-detail-link:hover {
        background: var(--org-olive);
        border-color: var(--org-olive);
        color: #ffffff;
    }

    .org-empty {
        min-height: 320px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: var(--org-muted);
        font-weight: 560;
        padding: 32px;
    }

    .print-report {
        display: none;
    }

    .print-page {
        background: #ffffff;
        page-break-after: always;
        break-after: page;
    }

    .print-page:last-child {
        page-break-after: auto;
        break-after: auto;
    }

    .print-kop {
        display: grid;
        grid-template-columns: 115px 1fr 120px;
        align-items: center;
        gap: 14px;
        border-bottom: 3px solid var(--org-navy);
        padding-bottom: 10px;
        margin-bottom: 12px;
    }

    .print-logo {
        max-height: 54px;
        max-width: 110px;
        object-fit: contain;
    }

    .print-title {
        text-align: center;
        color: var(--org-navy);
        font-weight: 620;
        font-size: 18px;
        line-height: 1.25;
        text-transform: uppercase;
        margin: 0;
    }

    .print-subtitle {
        text-align: center;
        color: #374151;
        font-weight: 580;
        font-size: 12.5px;
        margin-top: 3px;
        text-transform: uppercase;
    }

    .print-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        color: #4b5563;
        font-size: 10px;
        font-weight: 540;
        margin-bottom: 10px;
    }

    .print-summary {
        display: flex;
        gap: 7px;
        flex-wrap: wrap;
    }

    .print-summary span {
        display: inline-flex;
        border: 1px solid #d1d5db;
        border-radius: 999px;
        padding: 3px 8px;
        background: #f9fafb;
    }

    @media (max-width: 1200px) {
        .org-normal-control-inner {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .org-control-filter-form {
            grid-template-columns: minmax(0, 1fr) auto;
        }
    }

    @media (max-width: 1100px) {
        .org-filter-panel {
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .org-filter-actions {
            justify-content: flex-start;
            flex-wrap: wrap;
        }
    }

    @media (max-width: 992px) {
        .org-metric-strip {
            grid-template-columns: 1fr;
        }

        .org-hero {
            padding: 22px 20px;
        }

        .org-title {
            font-size: 24px;
        }

        .org-header-actions {
            justify-content: flex-start;
            margin-top: 12px;
        }
    }

    @media (max-width: 768px) {
        .org-page {
            padding: 14px 10px 30px;
        }

        .org-chart-scroll {
            padding: 32px 16px 42px;
        }

        .org-normal-control-card {
            padding: 16px;
        }

        .org-control-filter-form {
            grid-template-columns: 1fr;
        }

        .org-filter-panel {
            padding: 16px;
        }

        .org-filter-actions {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr;
        }

        .org-filter-actions .org-btn-primary,
        .org-filter-actions .org-btn-secondary {
            width: 100%;
            justify-content: center;
        }

        .org-btn-light {
            width: 100%;
            justify-content: center;
            text-align: center;
        }

        .org-board-toolbar {
            align-items: flex-start;
        }

        .org-toolbar-actions {
            width: 100%;
        }

        .org-legend {
            width: 100%;
            justify-content: center;
        }

        .org-node {
            width: 166px;
            min-height: 116px;
        }

        .org-node-topbar,
        .org-node-status-row {
            min-height: 28px;
            padding: 6px 8px;
        }

        .org-node-status,
        .org-node-status-row .org-status-text {
            font-size: 8.4px;
            padding: 3px 7px;
        }

        .org-node-title {
            font-size: 9.3px;
            min-height: 44px;
            padding: 8px 9px 7px;
        }

        .org-node-holder {
            font-size: 8.6px;
            min-height: 34px;
        }

        .org-node-dept {
            font-size: 7.4px;
        }
    }

    @page {
        size: A4 landscape;
        margin: 8mm;
    }

    @media print {
        html,
        body {
            width: 297mm;
            min-height: 210mm;
            background: #ffffff !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .sidebar,
        .sidebar-page-rail,
        .topbar,
        .navbar,
        .org-page,
        .main-header,
        .app-header,
        header,
        .no-print {
            display: none !important;
        }

        .main-content,
        .page-content,
        .container,
        .container-fluid,
        .content-wrapper {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        .print-report {
            display: block !important;
            width: 100% !important;
        }

        .print-page {
            width: 100%;
            min-height: 190mm;
            overflow: hidden;
            padding: 0;
        }

        .print-chart-area {
            width: 100%;
            overflow: visible !important;
            padding: 8px 0 0;
        }

        .print-chart-scale {
            transform-origin: top left !important;
            display: inline-block;
        }

        .print-page.overview-page .print-chart-scale {
            transform: scale(.34);
        }

        .print-page.department-page .print-chart-scale {
            transform: scale(.52);
        }

        .print-page .org-tree {
            justify-content: flex-start;
        }

        .print-page .org-node {
            box-shadow: none !important;
            border: 1px solid #111827 !important;
            border-radius: 9px !important;
            width: 152px;
            min-height: 98px;
        }

        .print-page .org-node-title {
            font-size: 8.1px;
            min-height: 38px;
            padding: 6px;
        }

        .print-page .org-node-holder {
            font-size: 7.6px;
            min-height: 28px;
            padding: 5px 6px;
        }

        .print-page .org-node-dept {
            font-size: 6.8px;
            padding: 4px;
        }

        .print-page .org-node-count {
            font-size: 8px;
        }

        .print-page .org-detail-link {
            display: none !important;
        }

        .print-page .org-chart-scroll::before {
            display: none !important;
        }
    }
</style>

<div class="container-fluid org-page {{ $isFullscreen ? 'is-fullscreen' : '' }}">
    <div class="org-shell">
        @unless($isFullscreen)
            <div class="org-hero no-print">
                <div class="org-hero-inner">
                    <div>
                        <div class="org-eyebrow">Struktur Organisasi</div>
                        <h3 class="org-title">Struktur Jabatan Perusahaan</h3>
                    </div>
                </div>
            </div>

            <div class="org-normal-control-card no-print">
                <div class="org-normal-control-inner">
                    <div class="org-metric-strip">
                        <div class="org-metric-item filled">
                            <div class="org-metric-text">
                                <div class="org-metric-label">Jabatan Terisi</div>
                                <div class="org-metric-value">{{ $summary['filled'] ?? 0 }}</div>
                            </div>

                            <div class="org-metric-icon">
                                <i class="bi bi-person-check"></i>
                            </div>
                        </div>

                        <div class="org-metric-item vacant">
                            <div class="org-metric-text">
                                <div class="org-metric-label">Jabatan Vacant</div>
                                <div class="org-metric-value">{{ $summary['vacant'] ?? 0 }}</div>
                            </div>

                            <div class="org-metric-icon">
                                <i class="bi bi-person-dash"></i>
                            </div>
                        </div>

                        <div class="org-metric-item total">
                            <div class="org-metric-text">
                                <div class="org-metric-label">Total Jabatan</div>
                                <div class="org-metric-value">{{ $summary['total'] ?? 0 }}</div>
                            </div>

                            <div class="org-metric-icon">
                                <i class="bi bi-diagram-3"></i>
                            </div>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('struktur-jabatan.index') }}" class="org-control-filter-form">
                        <div class="org-filter-field">
                            <label class="form-label">Filter Departemen</label>

                            <select name="id_departemen" class="form-select">
                                <option value="">Semua Departemen</option>

                                @foreach($departemenList as $dep)
                                    <option value="{{ $dep->id_departemen }}"
                                        {{ (string) $idDepartemen === (string) $dep->id_departemen ? 'selected' : '' }}>
                                        {{ $dep->nama_departemen }}
                                        @if($dep->singkatan)
                                            ({{ $dep->singkatan }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="org-filter-actions">
                            <button class="org-btn-primary" type="submit">
                                <i class="bi bi-funnel"></i>
                                Tampilkan
                            </button>

                            <a href="{{ route('struktur-jabatan.index') }}" class="org-btn-secondary">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="org-fullscreen-filter no-print">
                <form method="GET" action="{{ route('struktur-jabatan.index') }}" class="org-filter-panel">
                    <input type="hidden" name="fullscreen" value="1">

                    <div class="org-filter-field">
                        <label class="form-label">Filter Departemen</label>

                        <select name="id_departemen" class="form-select">
                            <option value="">Semua Departemen</option>

                            @foreach($departemenList as $dep)
                                <option value="{{ $dep->id_departemen }}"
                                    {{ (string) $idDepartemen === (string) $dep->id_departemen ? 'selected' : '' }}>
                                    {{ $dep->nama_departemen }}
                                    @if($dep->singkatan)
                                        ({{ $dep->singkatan }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="org-filter-actions">
                        <button class="org-btn-primary" type="submit">
                            <i class="bi bi-funnel"></i>
                            Tampilkan
                        </button>

                        <a href="{{ route('struktur-jabatan.index', ['fullscreen' => 1]) }}" class="org-btn-secondary">
                            Reset
                        </a>

                        <button type="button" onclick="saveStructurePdf()" class="org-btn-secondary">
                            <i class="bi bi-file-earmark-pdf"></i>
                            Save PDF
                        </button>

                        <a href="{{ route('struktur-jabatan.index', $normalParams) }}" class="org-btn-secondary">
                            Keluar Full Screen
                        </a>
                    </div>
                </form>
            </div>
        @endunless

        <div class="org-board-card">
            <div class="org-board-toolbar">
                <div class="org-board-title-wrap">
                    <div class="org-board-icon">
                        <i class="bi bi-diagram-3"></i>
                    </div>

                    <div>
                        <h4 class="org-board-title">{{ $sheetTitle }}</h4>
                    </div>
                </div>

                <div class="org-toolbar-actions no-print">
                    <div class="org-legend">
                        <span class="org-legend-item">
                            <span class="org-dot org-dot-filled"></span>
                            Terisi
                        </span>

                        <span class="org-legend-item">
                            <span class="org-dot org-dot-vacant"></span>
                            Kosong
                        </span>
                    </div>

                    <button type="button" class="org-mini-btn" data-zoom="out">
                        <i class="bi bi-dash-lg"></i>
                    </button>

                    <button type="button" class="org-mini-btn" data-zoom="reset">
                        100%
                    </button>

                    <button type="button" class="org-mini-btn" data-zoom="in">
                        <i class="bi bi-plus-lg"></i>
                    </button>

                    <button type="button" class="org-mini-btn" onclick="saveStructurePdf()">
                        <i class="bi bi-file-earmark-pdf"></i>
                        Save PDF
                    </button>

                    @if($isFullscreen)
                        <a href="{{ route('struktur-jabatan.index', $normalParams) }}" class="org-mini-btn">
                            Keluar Full Screen
                        </a>
                    @else
                        <a href="{{ route('struktur-jabatan.index', $fullscreenParams) }}" class="org-mini-btn">
                            <i class="bi bi-arrows-fullscreen"></i>
                            Full Screen
                        </a>
                    @endif
                </div>
            </div>

            <div class="org-chart-scroll" id="orgChartScroll">
                @if($struktur->count())
                    <div class="org-chart-canvas" id="orgChartCanvas">
                        <div class="org-tree">
                            <ul>
                                @foreach($struktur as $node)
                                    @include('struktur-jabatan.node', ['node' => $node, 'depth' => 0])
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @else
                    <div class="org-empty">
                        Struktur jabatan belum tersedia. Pastikan versi jabatan sudah approved dan parent_jabatan sudah diisi.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ================= PRINT / SAVE PDF REPORT ================= --}}
<div class="print-report">
    @foreach(($printSections ?? collect()) as $section)
        <div class="print-page {{ ($section['type'] ?? '') === 'overview' ? 'overview-page' : 'department-page' }}">
            <div class="print-kop">
                <div>
                    <img src="{{ $logoBsp }}" class="print-logo" alt="Logo BSP" onerror="this.style.display='none'">
                </div>

                <div>
                    <h1 class="print-title">{{ $section['title'] ?? 'STRUKTUR ORGANISASI' }}</h1>
                    <div class="print-subtitle">{{ $section['subtitle'] ?? 'PT BUMI SIAK PUSAKO' }}</div>
                </div>

                <div style="text-align:right;">
                    <img src="{{ $logoSkk }}" class="print-logo" alt="Logo SKK Migas" onerror="this.style.display='none'">
                </div>
            </div>

            <div class="print-meta">
                <div>
                    Kode: {{ $section['code'] ?? '-' }} · Dicetak: {{ now()->format('d/m/Y H:i') }}
                </div>

                <div class="print-summary">
                    <span>Filled: {{ $section['summary']['filled'] ?? 0 }}</span>
                    <span>Vacant: {{ $section['summary']['vacant'] ?? 0 }}</span>
                    <span>Total: {{ $section['summary']['total'] ?? 0 }}</span>
                </div>
            </div>

            <div class="print-chart-area">
                @if(($section['struktur'] ?? collect())->count())
                    <div class="print-chart-scale">
                        <div class="org-tree">
                            <ul>
                                @foreach($section['struktur'] as $node)
                                    @include('struktur-jabatan.node', ['node' => $node, 'depth' => 0])
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @else
                    <div class="org-empty">
                        Struktur jabatan belum tersedia.
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const isFullscreen = @json($isFullscreen);
        const body = document.body;
        const scrollBox = document.getElementById('orgChartScroll');
        const canvas = document.getElementById('orgChartCanvas');
        let zoom = 1;

        if (isFullscreen) {
            body.classList.add('org-fullscreen-mode');
        }

        function applyZoom() {
            if (!canvas) return;
            canvas.style.transform = `scale(${zoom})`;
        }

        document.querySelectorAll('[data-zoom]').forEach(function (button) {
            button.addEventListener('click', function () {
                const action = button.dataset.zoom;

                if (action === 'in') {
                    zoom = Math.min(1.45, zoom + 0.1);
                } else if (action === 'out') {
                    zoom = Math.max(0.55, zoom - 0.1);
                } else {
                    zoom = 1;
                }

                applyZoom();
            });
        });

        if (scrollBox) {
            let isDown = false;
            let startX = 0;
            let startY = 0;
            let scrollLeft = 0;
            let scrollTop = 0;

            scrollBox.addEventListener('mousedown', function (e) {
                isDown = true;
                startX = e.pageX - scrollBox.offsetLeft;
                startY = e.pageY - scrollBox.offsetTop;
                scrollLeft = scrollBox.scrollLeft;
                scrollTop = scrollBox.scrollTop;
                scrollBox.classList.add('is-dragging');
            });

            scrollBox.addEventListener('mouseleave', function () {
                isDown = false;
                scrollBox.classList.remove('is-dragging');
            });

            scrollBox.addEventListener('mouseup', function () {
                isDown = false;
                scrollBox.classList.remove('is-dragging');
            });

            scrollBox.addEventListener('mousemove', function (e) {
                if (!isDown) return;

                e.preventDefault();

                const x = e.pageX - scrollBox.offsetLeft;
                const y = e.pageY - scrollBox.offsetTop;
                const walkX = x - startX;
                const walkY = y - startY;

                scrollBox.scrollLeft = scrollLeft - walkX;
                scrollBox.scrollTop = scrollTop - walkY;
            });
        }
    });

    function saveStructurePdf() {
        window.print();
    }
</script>
@endsection