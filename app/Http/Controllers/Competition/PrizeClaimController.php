<?php

namespace App\Http\Controllers\Competition;

use App\Http\Controllers\Controller;
use App\Models\Competition\CompetitionWinner;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrizeClaimController extends Controller
{
    /**
     * Show claim form
     */
    public function show(string $code): View
    {
        $winner = CompetitionWinner::where('claim_code', $code)
            ->with(['competitionBranch', 'participant', 'period'])
            ->firstOrFail();

        if ($winner->prize_claimed) {
            return view('competition.claim.already-claimed', compact('winner'));
        }

        if (!$winner->canClaim()) {
            return view('competition.claim.expired', compact('winner'));
        }

        return view('competition.claim.form', compact('winner'));
    }

    /**
     * Submit claim
     */
    public function submit(Request $request, string $code)
    {
        $winner = CompetitionWinner::where('claim_code', $code)->firstOrFail();

        if ($winner->prize_claimed || !$winner->canClaim()) {
            return back()->with('error', 'لا يمكن استلام هذه الجائزة');
        }

        $validated = $request->validate([
            'bank_name' => 'required|string|max:100',
            'iban' => ['required', 'string', 'regex:/^SA\d{22}$/'],
            'confirm_iban' => 'required|same:iban',
            'accept_terms' => 'required|accepted',
        ], [
            'iban.regex' => 'رقم الآيبان غير صحيح. يجب أن يبدأ بـ SA ويتبعه 22 رقم',
            'confirm_iban.same' => 'رقم الآيبان غير متطابق',
            'bank_name.required' => 'يرجى اختيار البنك',
            'accept_terms.accepted' => 'يجب الموافقة على الشروط والأحكام',
        ]);

        $winner->update([
            'bank_name' => $validated['bank_name'],
            'iban' => $validated['iban'],
            'prize_claimed' => true,
            'prize_claimed_at' => now(),
            'claim_method' => 'bank_transfer',
        ]);

        return redirect()->route('competition.claim.success', ['code' => $code]);
    }

    /**
     * Show success page
     */
    public function success(string $code): View
    {
        $winner = CompetitionWinner::where('claim_code', $code)
            ->with(['competitionBranch', 'participant'])
            ->firstOrFail();

        return view('competition.claim.success', compact('winner'));
    }
}
