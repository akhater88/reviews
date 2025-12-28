<?php

namespace App\Http\Middleware;

use App\Models\Competition\CompetitionParticipant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CompetitionAuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $participantId = Session::get('competition_participant_id');

        if (!$participantId) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'يرجى تسجيل الدخول أولاً',
                    'requires_auth' => true,
                ], 401);
            }

            return redirect()->route('competition.landing')
                ->with('error', 'يرجى تسجيل الدخول أولاً');
        }

        $participant = CompetitionParticipant::find($participantId);

        if (!$participant) {
            Session::forget('competition_participant_id');
            Session::forget('competition_participant');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'الجلسة منتهية. يرجى تسجيل الدخول مرة أخرى.',
                    'requires_auth' => true,
                ], 401);
            }

            return redirect()->route('competition.landing')
                ->with('error', 'الجلسة منتهية');
        }

        if ($participant->is_blocked) {
            Session::forget('competition_participant_id');
            Session::forget('competition_participant');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'تم حظر حسابك من المشاركة.',
                ], 403);
            }

            return redirect()->route('competition.landing')
                ->with('error', 'تم حظر حسابك من المشاركة');
        }

        if (!$participant->phone_verified_at) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'يرجى التحقق من رقم الجوال أولاً',
                    'requires_verification' => true,
                ], 401);
            }

            return redirect()->route('competition.landing')
                ->with('error', 'يرجى التحقق من رقم الجوال');
        }

        // Add participant to request for easy access
        $request->merge(['competition_participant' => $participant]);

        return $next($request);
    }
}
