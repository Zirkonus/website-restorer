@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Dashboard</div>

                    <div class="panel-body">
                        <form role="form" class="form-horizontal" method="GET" action='/versions'>
                            <div class="form-group">
                                <label> Enter site URL</label>
                                <input class="form-control" placeholder="http://" name="site_url">
                            </div>
                            <button type="submit" class="btn btn-default">Search</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
