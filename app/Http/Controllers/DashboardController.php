<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\User;
use App\Models\DocumentMapping;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Hitung total dokumen
        $totalDocuments = DocumentMapping::count();

        $needReviewDocuments = DocumentMapping::whereHas('status', function ($q) {
            $q->where('name', 'Need Review');
        })->count();

        $activeDocuments = DocumentMapping::whereHas('status', function ($q) {
            $q->where('name', 'Active');
        })->count();

        // Hitung berdasarkan status
        $documentControls = DocumentMapping::whereHas('document', function ($q) {
            $q->where('type', 'control');
        })->count();

        $documentReviews = DocumentMapping::whereHas('document', function ($q) {
            $q->where('type', 'review');
        })->count();

        // Hitung user
        $totalUsers = User::count();

        // Ambil 5 dokumen terbaru
        $latestDocuments = DocumentMapping::with('status')
            ->latest()
            ->take(5)
            ->get();

        $obsoleteDocuments = DocumentMapping::whereHas('status', function ($q) {
            $q->where('name', 'Obsolete');
        })->get();

        return view('contents.index', compact(
            'totalDocuments',
            'needReviewDocuments',
            'documentControls',
            'documentReviews',
            'activeDocuments',
            'totalUsers',
            'latestDocuments',
            'obsoleteDocuments'
        ));
    }
}
