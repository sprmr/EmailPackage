@component('mail::layout')
    @slot('header')
        @component('mail::header', ['url' => env('SITE_URL') ])
            {{env('SITE_TITLE')}}
        @endcomponent
    @endslot
    {{ __('Hlo ')}},
	{{__('')}}<br><br>
    {!! __($msg) !!}  
   
    

    @slot('footer')  
        @component('mail::footer') 
        	{{__('Thanks')}},<br>   
        	{{env('SITE_TITLE')}}
        @endcomponent
    @endslot
@endcomponent


