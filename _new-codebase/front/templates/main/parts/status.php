<?php
function getRejectFormHTML($repairID)
{
    return '<form method="POST" id="reject-repair-form" action="/status/?ajax=save-reject-form">

    <h3 style="margin-bottom: 16px;">Пожалуйста, укажите причину отклонения:</h3>

    <div>
       <textarea style="
       min-width: 600px;
       min-height: 150px;
       border-radius: 7px;
       margin-bottom: 16px;
       padding: 15px 20px;
    overflow: auto;
    border: 1px solid #dde2ea;
    font-size: 20px;
    color: #0a1627;
    font-weight: 300;\;" name="message"></textarea>
    </div>   
 

    <div>
        <input type="hidden" name="repair_id" value="' . $repairID . '">
        <button type="submit" style="
        padding: 0 32px;
        float: right;
        border-radius: 7px;    
        height: 47px;
        background: #80bd03;
        font-size: 17px;
        color: #fff;border: 0;
        cursor: pointer;">
            Отклонить ремонт
        </button>
    </div>
</form>';
}
