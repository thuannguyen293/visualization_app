@extends('app.layouts.app_two_column')

@section('content')
    <img src="/images/contact_pic.png" class="img-fluid img-head" alt="Responsive image">
    <h4 class="mb-3">{{__('app.contact')}}</h4>
    <p>{!!htmlentities(__('app.description_contact'))!!}</p>
    <form class="form needs-validation contact-form">

        <div class="mb-3">
            <label for="username">{{__('app.username')}}</label>
            <input type="text" class="form-control" id="username" required="">
            <div class="invalid-feedback" style="width: 100%;">
                {{__('validation.required',['attribute'=>__('app.username')])}}
            </div>
        </div>

        <div class="mb-3">
            <label for="email">{{__('app.email_address')}}</label>
            <input type="email" class="form-control" id="email" placeholder="you@example.com">
            <div class="invalid-feedback">
                {{__('validation.required',['attribute'=>__('app.email_address')])}}
            </div>
        </div>

        <div class="mb-3">
            <label for="content">{{__('app.your_message')}}</label>
            <textarea class="form-control" id="content" required rows="4"></textarea>
            <div class="invalid-feedback">
                {{__('validation.required',['attribute'=>__('app.your_message')])}}
            </div>
        </div>


        {{--<hr class="mb-4">--}}
        <button class="btn btn-primary btn-lg btn-block btn-send" type="submit">{{__('app.send')}}</button>
    </form>

@endsection
