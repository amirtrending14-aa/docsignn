<?php

use App\Models\Document;
use App\Models\DocumentComment;
use App\Models\User;
use App\Policies\CommentPolicy;

it('allows the comment author and document owner to delete a comment only', function () {
    $author = new User;
    $author->id = 10;

    $owner = new User;
    $owner->id = 20;

    $otherUser = new User;
    $otherUser->id = 30;

    $document = new Document;
    $document->created_by = $owner->id;

    $comment = new DocumentComment;
    $comment->user_id = $author->id;
    $comment->setRelation('document', $document);

    $policy = new CommentPolicy;

    expect($policy->delete($author, $comment))->toBeTrue()
        ->and($policy->delete($owner, $comment))->toBeTrue()
        ->and($policy->delete($otherUser, $comment))->toBeFalse();
});
