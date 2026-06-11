<div class="container">
    <section class="admin__title">                
        <h5>Fabrics</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li><a href="{{route('product.view')}}">Product</a></li>
            <li>Fabrics</li>
            <li class="back-button">
                <a href="{{route('product.view')}}" class="btn btn-sm btn-danger select-md text-light font-weight-bold mb-0"><i class="material-icons text-white" style="font-size: 15px;">chevron_left</i>
                <span class="ms-1">Back</span> </a>
            </li>
          </ul>
    </section>
    <div class="row mb-4">
        <div class="col-lg-12 col-md-6 mb-md-0 mb-4">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header pb-0">
                            <div class="row">
                                @if(session()->has('message'))
                                    <div class="alert alert-success" id="flashMessage">
                                        {{ session('message') }}
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                <div class=" d-flex align-items-right justify-content-end">
                                    <a href="{{route('product.update', $product->id)}}" class="btn btn-sm btn-success select-md text-light font-weight-bold mb-0">+&nbsp; Add Fabric</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0" >
                                    @if($productFabrics->count()>0)
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Image</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Title</th>
                                            </tr>
                                        </thead>
                                        <tbody id="">
                                        
                                            @foreach ($productFabrics as $fabric)
                                            
                                                <tr data-id="{{ $fabric->id }}" class="handle">
                                                    <td class="align-middle">
                                                            @if ($fabric->image)
                                                                <img src="{{ asset($fabric->image) }}" alt="Fabric Image" width="70" style="border-radius: 10px;">
                                                            @else
                                                                <img src="{{ asset('assets/img/fabric.webp') }}" alt="Fabric Image" width="70" style="border-radius: 10px;">
                                                            @endif
                                                    </td>
                                                    <td><h6 class="mb-0 text-sm">{{ ucwords($fabric->title) }}</h6></td>
                                                    
                                                </tr>
                                            @endforeach
                                        
                                        </tbody>
                                    @else
                                        <p class="text-danger">No Fabric found !</p>
                                    @endif
                                </table>
                               
                                <div class="d-flex justify-content-end mt-2">
                                {{ $productFabrics->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>