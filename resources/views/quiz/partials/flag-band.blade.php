{{-- Green, white, green across the top edge. Thin enough never to cover anything. --}}
@if ($quiz->theme === \App\Enums\QuizTheme::Nigeria)
    <div class="flag-band" aria-hidden="true"
         style="display:flex;flex:none;height:1.1vh;min-height:5px;width:100%;">
        <span style="flex:1;background:#008751"></span>
        <span style="flex:1;background:#FFFFFF"></span>
        <span style="flex:1;background:#008751"></span>
    </div>
@endif
