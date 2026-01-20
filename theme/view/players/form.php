<!-- Centrēta forma ar ierobežotu platumu -->
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card">
            <div class="card-header">
                <h1 class="h3 mb-0 d-inline-block"><?php echo $title; ?></h1>

                <a href="/players" class="btn btn-secondary float-end">
                    <i class="bi bi-arrow-left"></i> Atpakaļ
                </a>
            </div>
            <div class="card-body">
                <form action="/players/save/<?php echo $player['id'] ?? ''; ?>" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($player['username'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($player['email'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="<?php echo $player['id'] ?? false ? 'Atstāj tukšu, lai nemainītu' : ''; ?>">
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Saglabāt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>