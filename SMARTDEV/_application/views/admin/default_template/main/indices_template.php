<div>
   <div class="panel-hdr color-success-600">
        <h2>
            _cat <span class="fw-300"><i>/indices</i></span>
        </h2>
        <div class="panel-toolbar">
            <button class="btn btn-panel waves-effect waves-themed" data-action="panel-fullscreen" data-toggle="tooltip" data-offset="0,10" data-original-title="Fullscreen"></button>
        </div>
    </div>
    <div class="panel-container collapse show">
        <div class="panel-content" style="max-height:363px;">

            <div class="panel-tag">Elastic 내에 Index 현황 </div>

            <div class="frame-wrap">
                <table class="table table-sm bg-fusion-900 table-bordered m-0 text-center">
                    <thead class="bg-fusion-500">
                        <tr class="color-info-400">
                            <th v-for="(info, key) in data[0]">{{ key }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="color-success-500" v-for="(value, key) in data" v-if="isUserIndex(value)">
                            <td v-for="(v, k) in value"><div v-html="checkValue(v)"></div></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
