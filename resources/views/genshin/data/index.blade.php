@extends('layouts.genshin')

@section('content')
    {{-- {{ dd($thum_img) }} --}}
    <div class="position-fixed top-0 start-0">
        <input type="button" value="選池" @click="toSetCurPool">
    </div>
    <div v-if="set_cur_pool" class="position-fixed start-0% h-100">

    </div>
    <div class="wish_interface_container d-flex flex-column justify-content-center align-items-center">
        <div class="d-flex justify-content-center align-items-center flex-wrap my-3">
            @foreach ($thum_img as $img)
                <img class="col" src="{{ $img }}" alt="thum_img">
            @endforeach
        </div>

        <div class="d-flex align-items-center justify-content-center my-3">
            @foreach ($main_img as $img)
                <img class="col-6" src="{{ $img }}" alt="thum_img">
            @endforeach

        </div>
        <div class="d-flex justify-content-evenly my-3">
            <div class="row container">
                <div class="col">
                    <div class="text-left">
                        <select name="cur_pool" class="form-control" v-model="cur_pool" @change="set_cur_pool()">
                            @foreach ($pool_all as $id => $pool)
                                <option value="{{ $id }}">
                                    {{ $pool['cr_name'] . ' ' . $pool['version'] . ' ' . $pool['pool_name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
