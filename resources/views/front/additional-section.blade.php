@php
   $all_padding_top_bottom = \App\Traits\CustomSection::AdminFrontHomePage();
@endphp

<section class="custom-section {{ $possition }}">
   <div class="container">
      <div class="row">
         <div class="col-12">
            <div class="section-title mw-100" data-aos="fade-up" data-aos-delay="100">
               <h2 class="title">{{ @$data->section_name }}</h2>
            </div>
            <div data-aos="fade-up" data-aos-delay="100">
               {!! @$data->content !!}
            </div>
         </div>

      </div>
   </div>
</section>
