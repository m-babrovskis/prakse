
    <!-- Main Content -->
    <main class="content-wrapper">
        <div class="container">
            <?php if (isset($content)): ?>
                <?php echo $content; ?>
            <?php else: ?>
                <!-- Default content area -->
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <h4 class="alert-heading">
                                <i class="bi bi-info-circle"></i> Sveiki!
                            </h4>
                            <p>Šis ir GameCore sistēmas base layout ar Bootstrap un jQuery.</p>
                            <hr>
                            <p class="mb-0">Lieto <code>$content</code> mainīgo, lai ievadītu lapas saturu.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Example Cards -->
                <div class="row mt-4">
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-speedometer2"></i> Ātrs
                                </h5>
                                <p class="card-text">Optimizēta veiktspēja un ātra ielāde.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-shield-check"></i> Drošs
                                </h5>
                                <p class="card-text">Prepared statements un drošības funkcijas.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-code-slash"></i> Moderns
                                </h5>
                                <p class="card-text">Bootstrap 5 un jQuery integrācija.</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

