<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudioAiGeneration;
use App\Models\StudioDesign;
use App\Support\StudioDesignPreview;
use App\Support\StudioPricingSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StudioAiGenerationController extends Controller
{
    public function index(Request $request): View
    {
        $query = StudioAiGeneration::query()
            ->with('user:id,name,email')
            ->orderByDesc('id');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($inner) use ($search) {
                $inner->where('prompt', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function ($user) use ($search) {
                        $user->where('email', 'like', '%'.$search.'%')
                            ->orWhere('name', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($request->query('hidden') === '1') {
            $query->where('hidden_from_customer', true);
        } elseif ($request->query('hidden') === '0') {
            $query->where('hidden_from_customer', false);
        }

        return view('admin.studio-ai.index', [
            'generations' => $query->paginate(24)->withQueryString(),
        ]);
    }

    public function hide(StudioAiGeneration $studioAiGeneration): RedirectResponse
    {
        $studioAiGeneration->update([
            'hidden_from_customer' => ! $studioAiGeneration->hidden_from_customer,
        ]);

        $label = $studioAiGeneration->hidden_from_customer ? 'hidden from customers.' : 'visible to the owner again.';

        return back()->with('success', 'Generation '.$label);
    }

    public function promote(Request $request, StudioAiGeneration $studioAiGeneration): RedirectResponse
    {
        $images = $studioAiGeneration->images();
        $index = (int) $request->input('image_index', 0);
        $url = $images[$index] ?? null;
        if (!$url) {
            return back()->with('error', 'Choose a design image to add to the library.');
        }

        $name = Str::limit(trim($studioAiGeneration->prompt), 80);
        if (count($images) > 1) {
            $name .= ' ('.($index + 1).')';
        }

        StudioDesign::create([
            'user_id' => $request->user()->id,
            'name' => $name,
            'image_url' => $url,
            'preview_url' => StudioDesignPreview::fromUrl($url),
            'tag' => 'AI',
            'price' => StudioPricingSettings::resolved()['library_default_price'],
            'is_active' => true,
            'sort_order' => 0,
        ]);

        return redirect()->route('admin.studio-designs.index')->with('success', 'Added the selected design to the library.');
    }

    public function destroy(StudioAiGeneration $studioAiGeneration): RedirectResponse
    {
        $studioAiGeneration->delete();

        return back()->with('success', 'Generation deleted.');
    }
}
