@extends('adminlte::page')

@section('title', 'TapiCarpas')

@section('content_header')
    <h1>{{$modo}}Herramienta</h1>

@stop

@section('content')

    <div class="form-gourp">

        @if(count($errors)>0)
            <div class="alert alert-danger" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{$error}}</li>
                    @endforeach
                </ul>

            </div>
        @endif

        <label for="Nombre">Nombre</label>
        <input type="text" class="form-control" name="Nombre" value="{{isset($herramienta->Nombre)?$herramienta->Nombre:old('Nombre')}}" id="Nombre">


        <label for="Descripcion">Descripción</label>
        <input type="text" class="form-control" name="Descripcion" value="{{isset($herramienta->Descripcion)?$herramienta->Descripcion:old('Descripcion')}}" id="Descripcion">

        <br/>

        <input class="btn btn-outline-success" type="submit" value="{{$modo}} datos">
        <a href="{{url('herramienta/')}}">Regresar</a>

    </div>

@stop

@section('css')

@stop

@section('js')
    <script> console.log('Hi!'); </script>
@stop



