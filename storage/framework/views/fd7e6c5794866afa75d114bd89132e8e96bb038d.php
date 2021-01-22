<div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
    <div class="container-fluid">
        <div class="header-body">
            <!-- Card stats -->
            <div class="row">
                <div class="col-xl-3 col-lg-6">
                    <div class="card card-stats mb-4 mb-xl-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0"><?php echo e(__('Total Completed Orders')); ?> ( 30 <?php echo e(__('days')); ?> )</h5>
                                    <span class="h2 font-weight-bold mb-0"><?php echo e($last30daysOrders); ?></span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-yellow text-white rounded-circle shadow">
                                        <i class="fas fa-shopping-basket"></i>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6">
                    <div class="card card-stats mb-4 mb-xl-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0"><?php echo e(__('Total Revenue')); ?> ( 30 <?php echo e(__('days')); ?> )</h5>
                                    <span class="h2 font-weight-bold mb-0"> <?php echo money($last30daysOrdersValue, env('CASHIER_CURRENCY','usd'),env('DO_CONVERTION',true)); ?></span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6">
                    <div class="card card-stats mb-4 mb-xl-0">
                        <div class="card-body">
                            <?php if(auth()->check() && auth()->user()->hasRole('admin')): ?>
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title text-uppercase text-muted mb-0"><?php echo e(__('Number of CHEFS')); ?></h5>
                                        <span class="h2 font-weight-bold mb-0"><?php echo e($countItems); ?> <?php echo e(__('CHEFS')); ?></span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                                            <i class="fas fa-folder"></i>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if(auth()->check() && auth()->user()->hasRole('owner')): ?>
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title text-uppercase text-muted mb-0"><?php echo e(__('Number of items')); ?></h5>
                                        <span class="h2 font-weight-bold mb-0"><?php echo e($countItems); ?> <?php echo e(__('items')); ?></span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                                            <i class="fas fa-folder"></i>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6">
                    <div class="card card-stats mb-4 mb-xl-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0"><?php echo e(__('Total CUSTOMERS')); ?></h5>
                                    <span class="h2 font-weight-bold mb-0"><?php echo e($allViews); ?> <?php echo e(__('Total CUSTOMERS')); ?></span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <br/>
            <?php if(auth()->check() && auth()->user()->hasRole('admin')): ?>
            <?php if(config('app.isft')): ?>
            <?php /*
            <div class="row">
                <div class="col-xl-3 col-lg-6">
                    <div class="card card-stats mb-4 mb-xl-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">{{ __('Delivery Fee') }} ( 30 {{ __('days') }} )</h5>
                                    <span class="h2 font-weight-bold mb-0"> @money($last30daysDeliveryFee, env('CASHIER_CURRENCY','usd'),env('DO_CONVERTION',true))</span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-yellow text-white rounded-circle shadow">
                                        <i class="fas fa-truck"></i>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6">
                    <div class="card card-stats mb-4 mb-xl-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">{{ __('Static Fee') }} ( 30 {{ __('days') }} )</h5>
                                    <span class="h2 font-weight-bold mb-0">@money($last30daysStaticFee, env('CASHIER_CURRENCY','usd'),env('DO_CONVERTION',true))</span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6">
                    <div class="card card-stats mb-4 mb-xl-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">{{ __('Dynamic Fee') }} ( 30 {{ __('days') }} )</h5>
                                    <span class="h2 font-weight-bold mb-0">@money($last30daysDynamicFee, env('CASHIER_CURRENCY','usd'),env('DO_CONVERTION',true))</span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                                        <i class="fas fa-percent"></i>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6">
                    <div class="card card-stats mb-4 mb-xl-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">{{ __('Total Fee') }} ( 30 {{ __('days') }} )</h5>
                                    <span class="h2 font-weight-bold mb-0">@money(( $last30daysTotalFee != null ? $last30daysTotalFee:0), env('CASHIER_CURRENCY','usd'),env('DO_CONVERTION',true))</span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                                        <i class="fas fa-coins"></i>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            */ ?>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH /var/www/html/homecook/resources/views/layouts/headers/cards.blade.php ENDPATH**/ ?>