<div class="row">
    <div class="col-lg-6">
        <h1 class="h3"><?php echo $title; ?></h1>
    </div>
    <div class="col-lg-6 text-end">
        <a href="/players/form" class="btn btn-primary">Add Player</a>
    </div>
</div>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($players as $player) { ?>
            <tr>
                <td><?php echo $player['id']; ?></td>
                <td><?php echo $player['username']; ?></td>
                <td><?php echo $player['email']; ?></td>
                <td>
                    <a href="/players/form/<?php echo $player['id']; ?>" class="btn btn-primary">Edit</a>
                    <a href="/players/delete/<?php echo $player['id']; ?>" class="btn btn-danger">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>