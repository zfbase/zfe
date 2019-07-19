<?php

class ZFE_Tasks_Performer_ErrorStub extends ZFE_Tasks_Performer
{
    public function perform(int $relatedItemId)
    {
        throw new ZFE_Tasks_Performer_Exception('error');
    }
}
