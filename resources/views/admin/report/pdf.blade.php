<html lang="ja">
    <head>
        <title>pdf output test</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <style>
            @font-face{
                font-family: migmix;
                font-style: normal;
                font-weight: normal;
                src: url("{{ storage_path('fonts/migmix-2p-regular.ttf')}}") format('truetype');
            }
            @font-face{
                font-family: migmix;
                font-style: bold;
                font-weight: bold;
                src: url("{{ storage_path('fonts/migmix-2p-bold.ttf')}}") format('truetype');
            }
            body {
                font-size: 12px;
                font-family: migmix;
                line-height: 80%;

            }
            .common {
                border: 1px solid #000;
                border-collapse: collapse;
                width: 100%;
                margin: 9px 0;
                page-break-inside: avoid; /* テーブル途中で改行しない */
                table-layout: fixed
            }
            .common th{
                background: #ddd;
                padding: 5px;
                border: 1px solid #000;

            }
            .common td{
                padding: 5px;
                border: 1px solid #000;

            }

.pagenum:before {
    content: counter(page);
}

            footer {
                position: fixed;
                bottom: -30px;
                left: 0px;
                right: 0px;
                height: 50px;

                text-align: right;
                line-height: 35px;
            }

        </style>
    </head>
    <body>
        <footer>
            株式会社 大川建設
        </footer>


        <table class="common">
            <tr>
                <th colspan="2">作業証明書</th>
                <th colspan="2">元請会社名</th>
                <td colspan="8">{{ $report->company->name }}</td>
            </tr>
            <tr>
                <td colspan="2">{{ $day }}</td>
                <th colspan="2">作業所名</th>
                <td colspan="8">{{ $report->site->name }}</td>
            </tr>
        </table>

@foreach(config('const.KOJINAMES') as $kojikey => $kojiname)
    @if (empty($report->{"koji_" . $kojikey . "_memo"})) @continue @endif
        <table class="common">
            <tr>
                <th colspan="2">作業区分</th>
                <th colspan="10">作業内容</th>
            </tr>
            <tr class="">
                <th colspan="2">{{ $kojiname }}</th>
                <td colspan="10">{!! nl2br(e($report->{"koji_" . $kojikey . "_memo"})) !!}</td>
            </tr>
            <tr>
                <th colspan="2">職種</th>
                <th colspan="3">稼働時間</th>
                <th colspan="3">員数</th>
                <th colspan="4">早残</th>
            </tr>
    @foreach(config('const.TOBI_DOKO') as $tobidokokey => $tobidokoname)
    @for ($ix = 1; $ix <= 5; $ix++)
    @if (
        empty($report->{"koji_" . $kojikey . "_" . $tobidokokey . "_" . $ix . "_sttime"})
        && empty($report->{"koji_" . $kojikey . "_" . $tobidokokey . "_" . $ix . "_edtime"})
        && empty($report->{"koji_" . $kojikey . "_" . $tobidokokey . "_" . $ix . "_num"})
        && empty($report->{"koji_" . $kojikey . "_" . $tobidokokey . "_" . $ix . "_sozan"})
    ) @continue
    @endif
            <tr>
                <th colspan="2">{{ $tobidokoname }}</th>
                <td colspan="3">
                    {{ Str::substr($report->{"koji_" . $kojikey . "_" . $tobidokokey . "_" . $ix . "_sttime"},0 ,5) }}
                    @if (isset($report->{"koji_" . $kojikey . "_" . $tobidokokey . "_" . $ix . "_sttime"}) || isset($report->{"koji_" . $kojikey . "_" . $tobidokokey . "_" . $ix . "_edtime"}))
                    ～
                    @endif
                    {{ Str::substr($report->{"koji_" . $kojikey . "_" . $tobidokokey . "_" . $ix . "_edtime"},0 ,5) }}
                </td>
                <td colspan="3">
                    {{ $report->{"koji_" . $kojikey . "_" . $tobidokokey . "_" . $ix . "_num"} }}
                    @if (isset($report->{"koji_" . $kojikey . "_" . $tobidokokey . "" . $ix . "__num"}))
                    人
                    @endif
                </td>
                <td colspan="4">
                    {{ $report->{"koji_" . $kojikey . "_" . $tobidokokey . "_" . $ix . "_sozan"} }}
                    @if (isset($report->{"koji_" . $kojikey . "_" . $tobidokokey . "_" . $ix . "_sozan"}))
                    H
                    @endif
                </td>
            </tr>
    @endfor
    @endforeach

    @if ($kojikey == config('const.KOJI.KOJI_CONCRETE'))
            <tr>
                <th colspan="2">総打設数量</th>
                <td colspan="10">
                    {{ $report->{"koji_" . $kojikey . "_dasetu"} }}
                    @if (isset($report->{"koji_" . $kojikey . "_dasetu"}))
                    ㎡
                    @endif
                </td>
            </tr>
    @endif




    @if ($kojikey == config('const.KOJI.KOJI_CONCRETE') || $kojikey == config('const.KOJI.KOJI_DOKO'))
        @for ($ix = 1; $ix <= 5; $ix++)
            @if ($kojikey == config('const.KOJI.KOJI_CONCRETE') && $ix >= 3)
            @break
            @endif
            <tr>
                <th colspan="2">
                    @if ($kojikey == config('const.KOJI.KOJI_CONCRETE'))
                    ポンプ
                    @elseif ($ix <= 3)
                    重機
                    @else
                    ダンプ
                    @endif
                </th>
                <td colspan="3">
                    {{ Str::substr($report->{"koji_" . $kojikey . "_car_" . $ix . "_sttime"},0 ,5) }}
                    @if (isset($report->{"koji_" . $kojikey . "_car_" . $ix . "_sttime"}) || isset($report->{"koji_" . $kojikey . "_car_" . $ix . "_edtime"}))
                    ～
                    @endif
                    {{ Str::substr($report->{"koji_" . $kojikey . "_car_" . $ix . "_edtime"},0 ,5) }}
                </td>
                <td colspan="3">
                    @php
                        $datasource = [];
                        if ($kojikey == config('const.KOJI.KOJI_CONCRETE')) {
                            // ポンプ
                            $datasource = config('const.PUMP');
                        } else if ($ix <= 3) {
                            // 重機
                            $datasource = config('const.JUKI');
                        } else {
                            // ダンプ
                            $datasource = config('const.DUMP');
                        }
                    @endphp
                    {{ $datasource[$report->{"koji_" . $kojikey . "_car_" . $ix . "_ton"}] }}
                    @if (isset($report->{"koji_" . $kojikey . "_car_" . $ix . "_ton"}))
                    ：
                    @endif
                    {{ $report->{"koji_" . $kojikey . "_car_" . $ix . "_num"} }}
                    @if (isset($report->{"koji_" . $kojikey . "_car_" . $ix . "_num"}))
                    台
                    @endif
                </td>
                <td colspan="4">
                    {{ $report->{"koji_" . $kojikey . "_car_" . $ix . "_sozan"} }}
                    @if (isset($report->{"koji_" . $kojikey . "_car_" . $ix . "_sozan"}))
                    H
                    @endif
                </td>
            </tr>
        @endfor
    @endif
        </table>
@endforeach


        <table class="common">
            <tr>
                <th colspan="12">作業員</th>
            </tr>
@foreach($workerlists as $workerlist)
            <tr>
    @for ($iy = 0; $iy < 6; $iy++)
                <td colspan="2">
                    {{ $workerlist[$iy]['name'] ?? '' }}
                    @if (isset($workerlist[$iy]['name']) || isset($workerlist[$iy]['tobidoko']))
                    ：
                    @endif
                    {{ $workerlist[$iy]['tobidoko'] ?? '' }}
                    @if (isset($workerlist[$iy]['sozan']))
                    ：{{ $workerlist[$iy]['sozan'] ?? '' }}H
                    @endif
                </td>
    @endfor
            </tr>
@endforeach



            <tr>
                <th colspan="12">運転者</th>
            </tr>
@foreach($driverlists as $driverlist)
            <tr>
    @for ($iy = 0; $iy < 6; $iy++)
                <td colspan="2">
                    {{ $driverlist[$iy]['name'] ?? '' }}
                </td>
    @endfor
            </tr>
@endforeach
        </table>


        <table class="common">
            <tr>
                <th colspan="12">承認</th>
            </tr>
            <tr>
                <td colspan="12" style="text-align: center;">
                    <img src="{{ asset( cacheBusting('storage/sign/' . $report->id . '.png') ) }}" />
                </td>
            </tr>

        </table>
    </body>
</html>
