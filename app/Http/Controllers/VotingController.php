<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Tariff;
use Illuminate\Http\Request;

class VotingController extends Controller
{
    /**
     * Show realized votings (list).
     */
    public function realized(Request $request)
    {
        $votings = []; // replace with DB fetch later
        return view('voting.realized', compact('votings'));
    }

    /**
     * Generic step handler for the creation wizard.
     * Route: /voting/create/step/{step}
     */
    public function step($step, Request $request)
    {
        $step = (int) $step;
        $stepNames = [
            1 => 'Choose Tariff',
            2 => 'Personal info & payments',
            3 => 'Insert reward',
            4 => 'Detail of event',
            5 => 'Creation of form',
        ];

        if ($step < 1 || $step > count($stepNames)) {
            abort(404);
        }

        // prepare variables
        $tariffs = null;
        $selectedTariff = null;
 
        // If session has a selected tariff, fetch it (useful both on step 1 and other steps)
        $selectedId = session('voting.selected_tariff');
        if ($selectedId) {
            $selectedTariff = Tariff::find($selectedId);
            // if tariff was removed since selecting, clear session
            if (!$selectedTariff) {
                session()->forget('voting.selected_tariff');
                $selectedTariff = null;
            }
        }
 
        if ($step === 1) {
            $tariffs = Tariff::orderBy('price_cents', 'asc')->get();
        } else {
            // For steps > 1, enforce that user has selected a tariff
            if (!$selectedTariff) {
                return redirect()->route('voting.create.step', ['step' => 1])->with('error', 'Please select a tariff first.');
            }
        }
 
        // Always pass both variables (tariffs may be null for steps > 1)
        return view('voting.step', [
            'currentStep' => $step,
            'stepNames' => $stepNames,
            'tariffs' => $tariffs,
            'selectedTariff' => $selectedTariff,
            'countries'   => $step == 2 ? Country::all() : null,
        ]);

        // // Tariff passed as query param from step1 selection
        // $tariff = $request->query('tariff', null);

        // return view('voting.step', [
        //     'currentStep' => $step,
        //     'stepNames'   => $stepNames,
        //     'tariff'      => $tariff,
        //     'countries'   => $step == 2 ? Country::all() : null,
        // ]);
    }

    public function selectTariff(Request $request)
    {
        $validated = $request->validate([
            'tariff' => 'required|exists:tariffs,id',
        ]);
 
        // cast to int and store
        $tariffId = (int) $validated['tariff'];
        session(['voting.selected_tariff' => $tariffId]);
 
        return redirect()->route('voting.create.step', ['step' => 2]);
    }
}
