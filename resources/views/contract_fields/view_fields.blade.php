@extends('layouts.app')
@section('content')
<h1>Fields of Contract {{$contract_type->name}} in Event {{$contract_type->Event->CODE}}</h1>
<div>
    <table>
        <thead>
            <th>Name of Field</th>
            <th>Type of Field</th>
        </thead>
        <tbody>
            @foreach ($fields as $field)
                <tr>
                    <td>{{$field->field_name}}</td>
                    <td>{{$field->field_type}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
