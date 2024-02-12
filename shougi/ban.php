<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <script>
    $teban=0;
    $is_pick_trun=1;
    $picked_id=-1;
    $put_id=-1;
    $tesuu=0;
    $end_tesuu=-1;
    $koma_name = {'O': '王', 'Q': '玉', 'H': '飛車', 'K': '角', 'k': '金', 'g': '銀', 'ki': '桂', 'ko': '香', 'h': '歩'};
    $narigoma_name = {'H': '龍', 'K': '馬', 'g': '成銀', 'ki': '成桂', 'ko': '成香', 'h': 'と'};
    $koma_id_list=[];

    $x0=200; // 盤面左上角
    $y0=100;
    $len=60; // 1マスの
    $total_state_log=[];
    $state={};
    function write_log() {
        $total_state = {};
        for($i=0; $i<$koma_id_list.length; ++$i) {
            $koma_id = $koma_id_list[$i];
            $element = document.getElementById($koma_id)
            $class = $element.className;
            $koma_info = {};
            $koma_info['kind'] = koma_kind($koma_id);
            $koma_info['state'] = koma_state($class);
            $koma_info['belonging'] = koma_belonging($class);
            $koma_info['position'] = position($element);
            $total_state[$koma_id] = $koma_info;
        }
        $total_state_log[$tesuu] = $total_state;
    }
    function min($x,$y) {
        if($x<$y) return $x;
        else return $y;
    }
    function max($x,$y) {
        if($x>$y) return $x;
        else return $y;
    }
    function teban_trans() {
        if($tesuu != 0) return;
        $s_element = document.getElementById('sente');
        $sx = $s_element.style.left;
        $sy = $s_element.style.top;
        $g_element = document.getElementById('gote');
        $gx = $g_element.style.left;
        $gy = $g_element.style.top;
        $s_element.style.left = $gx;
        $s_element.style.top = $gy;
        $g_element.style.left = $sx;
        $g_element.style.top = $sy;
        
        $s_img_element = document.getElementById('sente_img');
        $g_img_element = document.getElementById('gote_img');
        if($s_img_element.style.transform=="rotate(180deg)") $s_img_element.style.transform="";
        else $s_img_element.style.transform="rotate(180deg)";
        if($g_img_element.style.transform=="rotate(180deg)") $g_img_element.style.transform="";
        else $g_img_element.style.transform="rotate(180deg)";
        if($teban==0) $teban=1;
        else $teban=0;
    }
    function set_tesuu($x) {
        if($x==-3) $tesuu=0;
        if($x==-2) $tesuu=max($tesuu-5,0);
        if($x==-1) $tesuu=max($tesuu-1,0);
        if($x==1) $tesuu=min($tesuu+1,$end_tesuu);
        if($x==2) $tesuu=min($tesuu+5,$end_tesuu);
        if($x==3) $tesuu=$end_tesuu;
        if($x==0) {
            if($tesuu==0) return;
            $tesuu--;
            if($teban==0) $teban=1;
            else $teban=0;
        }
    }
    function total_state_show() {
        $total_state = $total_state_log[$tesuu];
        // 駒div
        $komadai_count = {'h':[0,0], 'ko':[0,0], 'ki':[0,0], 'k':[0,0], 'g':[0,0], 'H':[0,0], 'K':[0,0]};
        for($i=0; $i<$koma_id_list.length; ++$i) {
            $koma_id = $koma_id_list[$i];
            $koma_info = $total_state[$koma_id];
            if($koma_info['position']==-1) {// 駒台にある
                $komadai_count[$koma_info['kind']][$koma_info['belonging']]++;
                $komadai_id = String($koma_info['kind'])+"_"+String($koma_info['belonging']);
                document.getElementById($koma_id).style.left=document.getElementById($komadai_id).style.left;
                document.getElementById($koma_id).style.top=document.getElementById($komadai_id).style.top;
            }
            else {
                document.getElementById($koma_id).style.left=String($x0+($koma_info['position'][0]-1)*$len)+"px";
                document.getElementById($koma_id).style.top=String($y0+($koma_info['position'][1]-1)*$len)+"px";
            }
            document.getElementById($koma_id).className = "koma"+String($koma_info['belonging'])+"_"+String($koma_info['state']);
            // 駒画像
            $img_id = $koma_id+"_img";
            if($koma_info['state']==1) document.getElementById($img_id).src = "image/"+$narigoma_name[$koma_info['kind']]+".png";
            else document.getElementById($img_id).src = "image/"+$koma_name[$koma_info['kind']]+".png";
            if($koma_info['belonging']==1) document.getElementById($img_id).style.transform = "rotate(180deg)";
            else document.getElementById($img_id).style.transform = "";
        }
        // 駒台枚数表示
        for($koma_kind in $komadai_count) {
            for($i=0; $i<2; ++$i) {
                $count_id = "cnt_"+String($koma_kind)+"_"+String($i);
                if($komadai_count[$koma_kind][$i]<2) document.getElementById($count_id).style.display = "none";
                else document.getElementById($count_id).style.display = "";
                document.getElementById($count_id).textContent = String($komadai_count[$koma_kind][$i]);
            }   
        }
        // 手番マーク表示
        if($tesuu%2==0) {
            document.getElementById('sente').style.display="";
            document.getElementById('gote').style.display="none";
        }
        else {
            document.getElementById('sente').style.display="none";
            document.getElementById('gote').style.display="";
        }
        // 手数表示
        document.getElementById("tesuu").textContent = String($tesuu);
        // state復元
        get_state();
    }
    function get_state() {
        for($i=0; $i<9; ++$i) {
            for($j=0; $j<9; ++$j) {
                $state[[$i+1,$j+1]] = "none";
            }
        }
        for($i=0; $i<$koma_id_list.length; ++$i) {
            $koma_id = $koma_id_list[$i];
            $element = document.getElementById($koma_id);
            if(position($element)==-1) continue;
            $state[position($element)] = [$koma_id, koma_belonging($element.className)];
        }
    }
    function get_koma_id_list() {
        document.querySelectorAll(".koma0_0").forEach(element => {
            $koma_id_list[$koma_id_list.length] = element.id;
        });
        document.querySelectorAll(".koma1_0").forEach(element => {
            $koma_id_list[$koma_id_list.length] = element.id;
        });
    }
    function push($id) {
        if($tesuu==0 && $is_pick_trun) {
            get_koma_id_list(); 
            get_state();
            write_log();
        }
        if($is_pick_trun) pick($id);
        else {
            $put_id=$id;
            if(is_naru_chance($picked_id, $put_id)) naru_choice();
            else put();
        }
    }
    function koma_belonging($class) {return parseInt($class[4], 2);} // 0:先手の駒, 1:後手の駒
    function koma_state($class) {return parseInt($class[6], 3);} //0:盤上表, 1:盤上成駒 2:駒台
    function pick($id) {
        $class = document.getElementById($id).className;
        if($class.substring(0,4)!="koma") return;
        if(koma_belonging($class)!=$teban) return;
        point_show($id);
        $picked_id=$id;
        $is_pick_trun=0;
    }
    function position($element) {
        $x = 1 + (parseInt($element.style.left, 10)-$x0)/$len;
        $y = 1 + (parseInt($element.style.top, 10)-$y0)/$len;
        if(!(0<=$x && $x<=9)) return -1;
        if(!(0<=$y && $y<=9)) return -1;
        return [$x, $y];
    }
    function is_correct_choice($put_id) {
        if ($put_id==$picked_id) return 0;
        $class = document.getElementById($put_id).className;
        if (!($class=="masu" || $class.substring(0,4)=="koma")) return 0;
        $p = position(document.getElementById($put_id));
        return document.getElementById("point"+String($p[0])+String($p[1])).style.display!="none";
    }
    function finish() {
        $end_tesuu = $tesuu;
        if($teban==1) document.getElementById("sente_win").style.display="";
        else document.getElementById("gote_win").style.display="";
        document.querySelectorAll(".log").forEach(element => {
            element.style.display="";
        });
        document.getElementById("tesuu").style.fontSize = "30px";
        document.getElementById("tesuu").textContent = String($end_tesuu);
        document.getElementById("matta").style.display = "none";
        document.getElementById("toryo").style.display = "none";
    }
    function put() {
        $class = document.getElementById($put_id).className;
        $flag = is_correct_choice($put_id);
        $is_pick_trun=1;
        point_hide();
        if (!$flag) return;
        if (koma_state(document.getElementById($picked_id).className) == 2) {//打つ
            move();
            // class変更(state盤上表(0))
            if($teban==1) document.getElementById($picked_id).className = "koma1-0";
            else document.getElementById($picked_id).className = "koma0-0";
            // 駒台数字変更
            $komadai_id = String(koma_kind($picked_id)) + "_" + String($teban);
            $cnt_text = document.getElementById("cnt_"+$komadai_id).textContent;
            $new_count = parseInt($cnt_text,10)-1;
            document.getElementById("cnt_"+$komadai_id).textContent = String($new_count);
            if ($new_count <= 1) document.getElementById("cnt_"+$komadai_id).style.display="none";
        }
        else if ($class == 'masu') { //移動
            move();
        }
        else if (koma_belonging($class)!=$teban)  { //取る
            move();
            get();
        }
        // 王手の時
        $is_tumi=0;
        if (is_oote_real()) {
            if(is_tumi()) {
                $is_tumi=1;
                console.log("TUMI");
            }
            else console.log("OOTE")
        }
        // 手番交代
        if ($teban==0) $teban=1;
        else $teban=0;
        // マーク表示
        $element = document.getElementById('sente');
        if($element.style.display=="") $element.style.display="none";
        else $element.style.display="";
        $element = document.getElementById('gote');
        if($element.style.display=="") $element.style.display="none";
        else $element.style.display="";
        ++$tesuu; // 手数
        write_log(); // 状態を記録
        
        if($is_tumi) show_toryo_window();
    }
    function koma_kind($id_str) {
        $inital = $id_str.substring(0,1);
        if ($inital!='k') return $inital;
        if ($id_str.substring(0,2)=='ki') return 'ki';
        else if ($id_str.substring(0,2)=='ko') return 'ko';
        else return 'k';
    }
    function get() {
        $koma_id = $put_id;
        $element = document.getElementById($koma_id);
        $koma_kind = koma_kind($koma_id);
        $komadai_id = String($koma_kind) + "_" + String($teban);
        // class変更(state駒台(2))
        if($teban==1) document.getElementById($koma_id).className = "koma1-2";
        else document.getElementById($koma_id).className = "koma0-2";
        // 場所変更
        $element.style.left = document.getElementById($komadai_id).style.left;
        $element.style.top = document.getElementById($komadai_id).style.top;
        // 反転
        $transfrom = document.getElementById($koma_id+"_img").style.transform;
        if($transfrom == 'rotate(180deg)') document.getElementById($koma_id+"_img").style.transform = '';
        else document.getElementById($koma_id+"_img").style.transform ='rotate(180deg)';
        // オモテに戻す
        document.getElementById($koma_id+"_img").src = "image/"+$koma_name[$koma_kind]+".png";
        // 駒台数字変更
        $cnt=0;
        for($i=0; $i<$koma_id_list.length; ++$i) {
            if(koma_kind($koma_id_list[$i])!=$koma_kind) continue;
            $element = document.getElementById($koma_id_list[$i]);
            if($element.style.left == document.getElementById($komadai_id).style.left) ++$cnt;
        }
        document.getElementById("cnt_"+$komadai_id).textContent = String($cnt);
        if ($cnt > 1) document.getElementById("cnt_"+$komadai_id).style.display="";
        // $cnt_text = document.getElementById("cnt_"+$komadai_id).textContent;
        // $new_count = parseInt($cnt_text,10)+1;
        // document.getElementById("cnt_"+$komadai_id).textContent = String($new_count);
        // if ($new_count > 1) document.getElementById("cnt_"+$komadai_id).style.display="";
    }
    function move() {
        $koma_id=$picked_id;
        $block_id=$put_id;
        $koma_element = document.getElementById($koma_id);
        $block_element = document.getElementById($block_id);
        $koma_style = $koma_element.style;
        $block_style = $block_element.style;

        // $state更新
        if (koma_state($koma_element.className)!=2) {//
            $state[position($block_element)] = $state[position($koma_element)];
            $state[position($koma_element)] = "none";
        }
        else {
            $state[position($block_element)] = [$koma_element.id, koma_belonging($koma_element.className)];
        }

        // 表示場所更新
        $koma_element.style.left = $block_element.style.left;
        $koma_element.style.top = $block_element.style.top;
    }
    function is_nihu($i) {
        for($j=1; $j<=9; ++$j) {
            if($state[[$i,$j]]=="none") continue;
            if($state[[$i,$j]][1]==$teban 
                && koma_kind($state[[$i,$j]][0])=='h' 
                && koma_state(document.getElementById($state[[$i,$j]][0]).className)==0) return 1;
        }
        return 0;
    }
    function can_move_list($id_, $move_list, $is_check_tumi=0) {
        $element_ = document.getElementById($id_);
        $koma_belonging_ = koma_belonging($element_.className);
        $koma_state_ = koma_state($element_.className);
        $can_move_=[];
        if($teban==0) $next_teban=1;
        else $next_teban=0;
        if ($is_check_tumi) {
            if($teban==0) $next_teban=0;
            else $next_teban=1;
        }
        // 次に王さまを取られないか
        for($q=0; $q<$move_list.length; ++$q) {
            $pp = $move_list[$q];
            $next_state = Object.assign({}, $state);
            $next_state[$pp] = [$id_,$koma_belonging_];
            if($koma_state_!=2) $next_state[position($element_)] = "none";
            if(is_oote($next_teban, $next_state)) continue;
            $can_move_[$can_move_.length] = $pp;
        }
        // 二歩チェック
        $koma_kind_ = koma_kind($id_);
        if($koma_kind_=='h' && $koma_state_==2) {
            $move_list = $can_move_;
            $can_move_ = [];
            for($qq=0; $qq<$move_list.length; ++$qq) {
                $pp = $move_list[$qq];
                if(is_nihu_tumi($pp)) continue;
                $can_move_[$can_move_.length] = $pp;
            }
        }
        return $can_move_;
    }
    function move_list($id ,$state_) {
        $d = {
        'h': [[0,1]], 'ki':[[1,2],[-1,2]],
        'k': [[0,1],[1,1],[-1,1],[1,0],[-1,0],[0,-1]],
        'g': [[0,1],[1,1],[-1,1],[1,-1],[-1,-1]],
        'O': [[0,1],[1,1],[-1,1],[1,0],[-1,0],[-1,-1],[0,-1],[1,-1]],
        'Q': [[0,1],[1,1],[-1,1],[1,0],[-1,0],[-1,-1],[0,-1],[1,-1]],
        // 伸びがある
        'H': [[0,1], [0,-1], [1,0], [-1,0]],
        'K': [[1,1], [1,-1], [-1,1], [-1,-1]],
        'ko': [[0,1]]
        }
        $additional_d = {'H':[[1,1],[1,-1],[-1,1],[-1,-1]], 'K':[[0,1],[0,-1],[1,0],[-1,0]]};
        $element = document.getElementById($id);
        $koma_kind = koma_kind($id);
        $koma_belonging = koma_belonging($element.className);
        $koma_state = koma_state($element.className);
        $candidate = [];
        $move = [];
        $now_position = -1;
        if ($koma_state==2) {// 打つとき
            // 次に動ける場所が絶対ない場所でないか
            if ($koma_kind=='h' || $koma_kind=='ko') $max_y=8;
            else if ($koma_kind=='ki') $max_y=7;
            else $max_y=9;
            for($i=1; $i<=9; ++$i) {
                if($koma_kind=='h' && is_nihu($i)) continue;
                for($j=1; $j<=$max_y; ++$j) {
                    if($koma_belonging==0) $position=[$i,10-$j];
                    else $position=[$i,$j];
                    // 駒が置いていないか
                    if ($state_[$position]!= "none") continue;
                    $move[$move.length]=$position;
                }
            }
        }
        else {
            $now_position = position($element);
            if (($koma_kind!='H' && $koma_kind!='K' && $koma_kind!='ko') || ($koma_kind=='ko' && $koma_state==1)) {// 動きに伸びがない時
                if ($koma_state==1) $move_koma_kind = 'k';// 成駒
                else $move_koma_kind = $koma_kind;
                for ($i=0; $i<$d[$move_koma_kind].length; ++$i) {
                    $candidate[$candidate.length]=position_sum($now_position, $d[$move_koma_kind][$i], $koma_belonging);
                }
                for ($i=0; $i<$candidate.length; ++$i) {
                    $position = $candidate[$i];
                    // 範囲内か
                    if ($position[0]<1 || 9<$position[0]) continue;
                    if ($position[1]<1 || 9<$position[1]) continue;
                    // 自分の駒が置いていないか
                    if ($state_[$position]!= "none" && $state_[$position][1]==$koma_belonging) continue;
                    $move[$move.length] = $position;
                }
            }
            else {// 飛車・角・香車の候補
                for ($i=0; $i<$d[$koma_kind].length; ++$i) {
                    $p=$now_position;
                    while(1) {
                        $p = position_sum($p, $d[$koma_kind][$i], $koma_belonging);
                        if ($p[0]<1 || 9<$p[0]) break;
                        if ($p[1]<1 || 9<$p[1]) break;
                        if ($state_[$p]!="none") {
                            if ($state_[$p][1]!=$koma_belonging) $move[$move.length] = $p;
                            break;
                        }
                        $move[$move.length] = $p;
                    }
                }
                if ($koma_state==1) {// 馬か龍
                    for ($i=0; $i<$additional_d[$koma_kind].length; ++$i) {
                        $p = position_sum($now_position, $additional_d[$koma_kind][$i], $koma_belonging);
                        if ($p[0]<1 || 9<$p[0]) continue;// 範囲内
                        if ($p[1]<1 || 9<$p[1]) continue;
                        if ($state_[$p]!= "none" && $state_[$p][1]==$koma_belonging) continue; //自分のコマが置いてない
                        $move[$move.length] = $p;
                    }
                }
            }
        }
        return $move;
    }
    function point_show($id) {
        // // どこでも行ける
        // document.querySelectorAll(".point").forEach(element => {
        //     element.style.display="";
        // });
        // return;
        $can_move = can_move_list($id, move_list($id,$state));
        for ($i=0; $i<$can_move.length; ++$i) {
            $p = $can_move[$i];
            document.getElementById("point"+String($p[0])+String($p[1])).style.display="";
        }
    }
    function position_sum($position, $d, $koma_belonging) {
        if ($koma_belonging==0) return [$position[0]-$d[0],$position[1]-$d[1]];
        else return [$position[0]+$d[0],$position[1]+$d[1]];
    }
    function point_hide() {
        document.querySelectorAll(".point").forEach(element => {
            element.style.display="none";
        });
    }
    function naru_choice() {
        $display = document.getElementById("choice_window").style.display;
        if($display=="") $display="none";
        else $display="";
        document.getElementById("choice_window").style.display=$display;
    }
    function is_naru_chance($koma_id, $block_id) {
        $posi = position(document.getElementById($block_id));
        if($posi==-1) return 0;
        if(document.getElementById("point"+String($posi[0])+String($posi[1])).style.display=="none") return 0;
        if($koma_id==$block_id) return 0;
        $koma_kind = koma_kind($koma_id);
        // 成れる駒か
        if($koma_kind=='O') return 0;
        if($koma_kind=='Q') return 0;
        if($koma_kind=='k') return 0;
        // 成れる状態の駒か
        if(koma_state(document.getElementById($koma_id).className)!=0) return 0;
        // 成るしかない時
        $y_masu = 1 + (parseInt(document.getElementById($block_id).style.top, 10)-$y0)/$len;
        if ($koma_kind=='h' || $koma_kind=='ko') {
            if($teban==0 && $y_masu==1) {naru();return 0;}
            if($teban==1 && $y_masu==9) {naru();return 0;}
        }
        if ($koma_kind=='ki') {
            if($teban==0 && $y_masu<=2) {naru();return 0;}
            if($teban==1 && $y_masu>=8) {naru();return 0;}
        }
        // 元々成れる領域にいたか
        $y_masu_pre = 1 + (parseInt(document.getElementById($koma_id).style.top, 10)-$y0)/$len;
        if ($teban==0 && $y_masu_pre<=3) return 1;
        if ($teban==1 && $y_masu_pre>=7) return 1;
        // 成る領域であるか
        if ($teban==0) return $y_masu<=3;
        else return $y_masu>=7;
    }
    function naru() {
        // 画像変換
        $koma_kind = koma_kind($picked_id);
        document.getElementById($picked_id + "_img").src = "image/"+$narigoma_name[$koma_kind]+".png";
        // komastateを成駒(1)に
        $class = document.getElementById($picked_id).className;
        document.getElementById($picked_id).className= "koma" + koma_belonging($class) + "_1";
    }
    function hide_choice_window() {
        document.getElementById("choice_window").style.display="none";
    }
    function show_toryo_window() {
        document.getElementById("toryo_window").style.display="";
    }
    function hide_toryo_window() {
        document.getElementById("toryo_window").style.display="none";
    }
    function is_oote_real() {
        for($i=1; $i<=9; ++$i) {
            for($j=1; $j<=9; ++$j) {
                if($state[[$i,$j]]=="none") continue;
                if($state[[$i,$j]][0]=='O') $O_p = [$i,$j];
                if($state[[$i,$j]][0]=='Q') $Q_p = [$i,$j];
            }
        }
        if($teban==0) $aite_o_p = $Q_p;
        else $aite_o_p = $O_p;
        for($ii=1; $ii<=9; ++$ii) {
            for($jj=1; $jj<=9; ++$jj) {
                if($state[[$ii,$jj]]=="none") continue;
                if($state[[$ii,$jj]][1]!=$teban) continue;
                $move = move_list($state[[$ii,$jj]][0], $state);
                for($k=0; $k<$move.length; ++$k) {
                    if ($move[$k].toString()==$aite_o_p.toString()) {
                        return 1;
                    }
                }  
            }
        }
        return 0;
    }
    function is_oote($teban_, $state__) {
        for($i=1; $i<=9; ++$i) {
            for($j=1; $j<=9; ++$j) {
                if($state__[[$i,$j]][0]=='O') $O_p = [$i,$j];
                if($state__[[$i,$j]][0]=='Q') $Q_p = [$i,$j];
            }
        }
        if($teban_==0) $aite_o_p = $Q_p;
        else $aite_o_p = $O_p;
        for($ii=1; $ii<=9; ++$ii) {
            for($jj=1; $jj<=9; ++$jj) {
                if($state__[[$ii,$jj]]=="none") continue;
                if($state__[[$ii,$jj]][1]!=$teban_) continue;
                $move = move_list($state__[[$ii,$jj]][0], $state__);
                for($k=0; $k<$move.length; ++$k) {
                    if ($move[$k].toString()==$aite_o_p.toString()) {
                        return 1;
                    }
                }  
            }
        }
        return 0;
    }
    function is_tumi() {
        for($n=0; $n<$koma_id_list.length; ++$n) {
            if(koma_belonging(document.getElementById($koma_id_list[$n]).className)==$teban) continue;
            $can_move = can_move_list($koma_id_list[$n], move_list($koma_id_list[$n], $state), 1);
            for($k=0; $k<$can_move.length; ++$k) {
                return 0;
            } 
        }
        return 1;
    }
    function is_nihu_tumi($utihu_p) {
        for($i=1; $i<=9; ++$i) {
            for($j=1; $j<=9; ++$j) {
                if($state[[$i,$j]][0]=='O') $O_p = [$i,$j];
                if($state[[$i,$j]][0]=='Q') $Q_p = [$i,$j];
            }
        }
        if($teban==0) $aite_o_p = $Q_p;
        else $aite_o_p = $O_p;
        // 王手か
        if($teban==0) {
            if($aite_o_p[0]!=$utihu_p[0]) return 0;
            if($aite_o_p[1]!=$utihu_p[1]-1) return 0;
        }
        else {
            if($aite_o_p[0]!=$utihu_p[0]) return 0;
            if($aite_o_p[1]!=$utihu_p[1]+1) return 0;
        }
        $b1=0; // 打ち歩に紐がついている
        $b2=1; // 王様以外で打ち歩を取れない
        $b3=1; // 王様の逃げ場所がない
        $aite_o_move = move_list($state[$aite_o_p][0], $state);
        for($ii=1; $ii<=9; ++$ii) {
            for($jj=1; $jj<=9; ++$jj) {
                if($state[[$ii,$jj]]=="none") continue;
                if([$ii,$jj].toString()==$utihu_p.toString()) continue;
                if([$ii,$jj].toString()==$aite_o_p.toString()) continue;
                $move = move_list($state[[$ii,$jj]][0], $state);
                for($k=0; $k<$move.length; ++$k) {
                    if ($move[$k].toString()==$utihu_p.toString()) {
                        if($state[[$ii,$jj]][1]==$teban) $b1=1;
                        else $b2=0;
                    }
                    for($l=0; $l<$aite_o_move.length; ++$l) {
                        if($aite_o_move[$l]=="can't move") continue;
                        if ($move[$k].toString()==$aite_o_move[$l].toString()
                        && $state[[$ii,$jj]][1]==$teban) {
                            $aite_o_move[$l]="can't move";
                        }
                    }
                }
            }
        }
        for($l=0; $l<$aite_o_move.length; ++$l) {
            if($aite_o_move[$l]!="can't move") $b3=0;
        }
        return $b1*$b2*$b3;
    }
    </script>
</head>
<body>
<?php
    function make_block_div($id,$class,$x,$y,$w,$h,$image="",$is_rotate=FALSE) {
        global $z_index_komadai, $z_index_masu, $z_index_koma, $z_index_koma_counter;
        echo "<div id='" . $id . "' ".
        "class='" . $class . "' " .
        "onclick='push(" . '"'. $id . '" '.");' " .
        "style='position:absolute; ";
        if($class=="masu") echo "z-index:" . $z_index_masu ."; ";
        else if($class=="possession_masu") echo "z-index:" . $z_index_masu ."; ";
        else if($class=="counter") echo "z-index:" . $z_index_koma_counter ."; ";
        else if($class=="koma0_0" || $class=="koma1_0") echo "z-index:" . $z_index_koma ."; ";
        if($class == "counter") echo "display:none; ";
        echo "left:" . $x . "px; " .
        "top:" . $y . "px; " . 
        "width:" . $w . "px; " . 
        "height:" . $h . "px;'>";
        if($image!="") {
            if($is_rotate) echo "<img id='".$id."_img' src=". $image . " style='height: 100%; transform: rotate(180deg);'>";
            else echo "<img id='".$id."_img' src=". $image . " style='height: 100%;'>";
        } 
        if($class == "counter") echo "<div><a>0</a></div>";
        echo "</div>\n";
    }
    function make_point_div($id,$class,$x,$y,$w,$h) {
        global $z_index_point;
        echo "<div id='" . $id . "' " .
        "class='" . $class . "' " .
        "style='position:absolute; z-index:". $z_index_point ."; background-color:silver; display:none; " .
        "left:" . $x-$w/2 . "px; " .
        "top:" . $y-$h/2 . "px; " . 
        "width:" . $w . "px; " . 
        "height:" . $h . "px;'>" .
        "</div>\n";
    }
    function make_line_div() {
        global $z_index_line, $x0,$y0,$len, $komadai_w, $komadai_h;
        $line_cnt=0;
        for ($i=0; $i<10; ++$i) {
            // 横線
            echo "<p id='l" . $line_cnt++ . "' " .
            "class='line' " .
            "style='position:absolute; z-index:". $z_index_line .'; border-top: solid 1px; margin-Top: 0px; ' .
            "left:" . $x0 . "px; " .
            "top:" . $y0+$len*$i . "px; " .
            "width:" . $len*9 . "px; " .
            "height:" . 0 . "px;'>" .
            "</p>\n";
            // 縦線
            echo "<p id='l" . $line_cnt++ . "' " .
            "class='line' " .
            "style='position:absolute; z-index:". $z_index_line .'; border-left: solid 1px; margin-Top: 0px; ' .
            "left:" . $x0+$len*$i . "px; " .
            "top:" . $y0 . "px; " . 
            "width:" . 0 . "px; " . 
            "height:" . $len*9 . "px;'>" .
            "</p>\n";
        }
        // 駒台
        $komadai_start_point_yoko = [
            [$x0-$komadai_w, $y0],
            [$x0-$komadai_w, $y0+$komadai_h],
            [$x0+$len*9, $y0+$len*9-$komadai_h],
            [$x0+$len*9, $y0+$len*9],
        ];
        $komadai_start_point_tate = [
            [$x0-$komadai_w, $y0],
            [$x0+$len*9+$komadai_w, $y0+$len*9-$komadai_h],
        ];
        for($i=0; $i<count($komadai_start_point_yoko); ++$i) {
            echo "<p id='l" . $line_cnt++ . "' " .
            "class='line' " .
            "style='position:absolute; z-index:". $z_index_line ."; border-top: solid 1px; margin-Top: 0px; " .
            "left:" . $komadai_start_point_yoko[$i][0] . "px; " .
            "top:" . $komadai_start_point_yoko[$i][1] . "px; " .
            "width:" . $komadai_w . "px; " . 
            "height:" . 0 . "px;'>" .
            "</p>\n";
        }
        for($i=0; $i<count($komadai_start_point_tate); ++$i) {
            echo "<p id='l" . $line_cnt++ . "' " .
            "class='line' " .
            "style='position:absolute; z-index:". $z_index_line ."; border-left: solid 1px; margin-Top: 0px; " .
            "left:" . $komadai_start_point_tate[$i][0] . "px; " .
            "top:" . $komadai_start_point_tate[$i][1] . "px; " .
            "width:" . 0 . "px; " . 
            "height:" . $komadai_h . "px;'>" .
            "</p>\n";
        }
    }
    function init() {
        global $x0, $y0, $len, $z_index_window;
        // 盤と駒台(線)
        make_line_div();
        // マス
        for($i=0; $i<9; ++$i) {
            for($j=0; $j<9; ++$j) {  
                make_block_div(strval($i+1).strval($j+1), "masu", 
                $x0+$i*$len, $y0+$j*$len, $len, $len);
            }
        }
        // 移動印
        for($i=0; $i<9; ++$i) {
            for($j=0; $j<9; ++$j) {  
                make_point_div('point'.strval($i+1).strval($j+1), "point", 
                $x0+($i+0.5)*$len, $y0+($j+0.5)*$len, $len/12, $len/12);
            }
        }
        // 駒
        $name_translate = [
            '王'=>'O', '玉'=>'Q', 
            '飛車'=>'H', '角'=>'K', '金'=>'k','銀'=>'g',
            '桂'=>'ki','香'=>'ko','歩'=>'h'
        ];
        $sente = [
            'O' => ['王', 5, 9],
            'H1' => ['飛車', 8, 8], 'K1' => ['角', 2, 8],
            'k1' => ['金', 4, 9], 'k2' => ['金', 6, 9],
            'g1' => ['銀', 3, 9], 'g2' => ['銀', 7, 9],
            'ki1' => ['桂', 2, 9], 'ki2' => ['桂', 8, 9],
            'ko1' => ['香', 1, 9], 'ko2' => ['香', 9, 9],
            'h1' => ['歩', 1, 7], 'h2' => ['歩', 2, 7], 'h3' => ['歩', 3, 7], 
            'h4' => ['歩', 4, 7], 'h5' => ['歩', 5, 7], 'h6' => ['歩', 6, 7], 
            'h7' => ['歩', 7, 7], 'h8' => ['歩', 8, 7], 'h9' => ['歩', 9, 7] 
        ];
        $gote = [
            'Q' => ['玉', 5, 1],
            'H2' => ['飛車', 2, 2], 'K2' => ['角', 8, 2],
            'k3' => ['金', 6, 1], 'k4' => ['金', 4, 1],
            'g3' => ['銀', 7, 1], 'g4' => ['銀', 3, 1],
            'ki3' => ['桂', 8, 1], 'ki4' => ['桂', 2, 1],
            'ko3' => ['香', 9, 1], 'ko4' => ['香', 1, 1],
            'h10' => ['歩', 9, 3], 'h11' => ['歩', 8, 3], 'h12' => ['歩', 7, 3], 
            'h13' => ['歩', 6, 3], 'h14' => ['歩', 5, 3], 'h15' => ['歩', 4, 3], 
            'h16' => ['歩', 3, 3], 'h17' => ['歩', 2, 3], 'h18' => ['歩', 1, 3] 
        ];
        foreach($sente as $key => $val) {
            make_block_div($key, "koma0_0", 
            $x0+($val[1]-1)*$len, $y0+($val[2]-1)*$len, $len, $len, 'image/'.$val[0].'.png');
        }
        foreach($gote as $key => $val) {
            make_block_div($key, "koma1_0", 
            $x0+($val[1]-1)*$len, $y0+($val[2]-1)*$len, $len, $len, 'image/'.$val[0].'.png', TRUE);
        }
        // 持駒マス 
        $koma_counter_radio_x=4;
        $koma_counter_radio_y=2.5;
        $posseion_positions=[
            [9.3, 5.25, '飛車', 0], [-1.3, 2.75, '飛車', 1],
            [10.7, 5.25, '角', 0], [-2.7, 2.75, '角', 1],
            [9.3, 6.5, '金', 0], [-1.3, 1.5, '金', 1],
            [10.7, 6.5, '銀', 0], [-2.7, 1.5, '銀', 1],
            [9, 7.75, '桂', 0], [-1, 0.25, '桂', 1],  
            [10, 7.75, '香', 0], [-2, 0.25, '香', 1],
            [11, 7.75, '歩', 0], [-3, 0.25, '歩', 1]
        ];
        for($i=0; $i<count($posseion_positions); ++$i) {
            $buff = $posseion_positions[$i];
            make_block_div($name_translate[$buff[2]].'_'.$buff[3], "possession_masu", 
            $x0+$buff[0]*$len, $y0+$buff[1]*$len, $len, $len);
            if($buff[2]=='歩' && $buff[3]==0) {
                make_block_div("cnt_h".'_'.$buff[3], "counter", 
                $x0+($buff[0]+1)*$len-$len/$koma_counter_radio_x*1.6, $y0+$buff[1]*$len, 
                $len/$koma_counter_radio_x*1.6, $len/$koma_counter_radio_y);
            }
            else {
                make_block_div("cnt_".$name_translate[$buff[2]].'_'.$buff[3], "counter", 
                $x0+($buff[0]+1)*$len-$len/$koma_counter_radio_x, $y0+$buff[1]*$len, 
                $len/$koma_counter_radio_x, $len/$koma_counter_radio_y);
            }
        }
        // 手番マーク
        echo "<div id='sente' " .
        "class='" . 'mark' . "' " .
        "onclick='teban_trans();'" .
        "style='position:absolute; display:; " .
        "left:" . $x0+$len*9 . "px; " .
        "top:" . $y0+$len*4 . "px; " . 
        "width:" . $len . "px; " . 
        "height:" . $len . "px;'>" .
        "<img id='sente_img' src='image/先手.png' style='height: 100%;'>".
        "</div>\n";
        echo "<div id='gote' " .
        "class='" . 'mark' . "' " .
        "onclick='teban_trans();'" .
        "style='position:absolute; display:none; " .
        "left:" . $x0-$len*1 . "px; " .
        "top:" . $y0+$len*4 . "px; " . 
        "width:" . $len . "px; " . 
        "height:" . $len . "px;'>" .
        "<img id='gote_img' src='image/後手.png' style='height: 100%; transform: rotate(180deg);'>".
        "</div>\n";
        // 勝利マーク
        echo "<div id='sente_win' " .
        "class='" . 'mark' . "' " .
        "style='position:absolute; display:none; " .
        "left:" . $x0+$len*10 . "px; " .
        "top:" . $y0+$len*4 . "px; " . 
        "width:" . $len . "px; " . 
        "height:" . $len . "px;'>" .
        "<img id='sente_img' src='image/勝利.png' style='height: 100%;'>".
        "</div>\n";
        echo "<div id='gote_win' " .
        "class='" . 'mark' . "' " .
        "style='position:absolute; display:none; " .
        "left:" . $x0-$len*2 . "px; " .
        "top:" . $y0+$len*4 . "px; " . 
        "width:" . $len . "px; " . 
        "height:" . $len . "px;'>" .
        "<img id='sente_img' src='image/勝利.png' style='height: 100%; transform: rotate(180deg);'>".
        "</div>\n";
        // 成る・投了window
        $center_x = $x0+$len*4.5;
        $center_y = $y0+$len*4.5;
        $window_w = $len*4;
        $window_h = $len*2;
        $window_details=[
            ['choice_window', '成りますか?', 'naru(); hide_choice_window(); put();', 'hide_choice_window(); put();'],
            ['toryo_window', '投了しますか?', 'hide_toryo_window(); finish();','hide_toryo_window();']];
        for ($i=0; $i<2; ++$i) {
            echo "<div id='".$window_details[$i][0]."' " .
            "style='position:absolute; z-index:". $z_index_window .";  background-color:#F0F0F0FF; text-align:center; display:none; ".
            "left:" . $center_x-$window_w/2 . "px; " .
            "top:" . $center_y-$window_h/2 . "px; " . 
            "width:" . $window_w . "px; " . 
            "height:" . $window_h . "px;'>" .
            "<div id='naru_str' ".
            "style='position:absolute; left:" . 0 . "px; top:" . $len/12*5 . "px; " . 
            "width:" . $window_w . "px; height:" . $window_h . "px;'>".$window_details[$i][1]."</div>" .
            "<button id='yes' ".
            "style='position:absolute; left:" . $len . "px; top:" . $len*(1+1/6) . "px; " . 
            "width:" . $window_w/5 . "px; height:" . $window_h/5 . "px;'".
            "onclick='".$window_details[$i][2]."'>はい</button>" .
            "<button id='no' ".
            "style='position:absolute; left:" . $len*2 . "px; top:" . $len*(1+1/6) . "px; " . 
            "width:" . $window_w/4 . "px; height:" . $window_h/5 . "px;'".
            "onclick='".$window_details[$i][3]."'>いいえ</button>" .
            "</div>\n";
        }
        // 盤面動かすボタン
        $id_list = [['first', '端', 1, -3], ['pre_skip', 'スキップ', 1, -2], ['pre', '隣', 1, -1], 
                    ['next','隣',0, 1], ['next_skip', 'スキップ', 0, 2], ['last', '端', 0, 3]];
        for($i=0; $i<count($id_list); ++$i) {
            echo "<div id=".$id_list[$i][0]." " .
            "class='" . 'log' . "' " .
            "onclick='set_tesuu(".$id_list[$i][3]."); total_state_show();'".
            "style='position:absolute; display:none; " .
            "left:" . $x0+$len*($id_list[$i][3]+4+1/6) . "px; " .
            "top:" . $y0+$len*(9+1/6) . "px; " . 
            "width:" . $len/3*2 . "px; " . 
            "height:" . $len/3*2 . "px;'>" .
            "<img id='sente_img' src='image/".$id_list[$i][1].".png' style='height: 100%; ";
            if ($id_list[$i][2]==1) echo "transform: rotate(180deg);'>";
            else echo "'>";
            echo "</div>\n";
        }
        // 待ったボタン
        echo "<div id="."matta"." " .
        "onclick='set_tesuu(0); total_state_show();'".
        "style='position:absolute; display:; " .
        "left:" . $x0+$len*(7+1/6) . "px; " .
        "top:" . $y0+$len*(9+1/6) . "px; " . 
        "width:" . $len/3*2 . "px; " . 
        "height:" . $len/3*2 . "px;'>" .
        "<img id='sente_img' src='image/待.png' style='height: 100%; '>";
        echo "</div>\n";
        // 投了ボタン
        echo "<div id="."toryo"." " .
        "onclick='show_toryo_window();'".
        "style='position:absolute; display:; " .
        "left:" . $x0+$len*(8+1/6) . "px; " .
        "top:" . $y0+$len*(9+1/6) . "px; " . 
        "width:" . $len/3*2 . "px; " . 
        "height:" . $len/3*2 . "px;'>" .
        "<img id='sente_img' src='image/投了.png' style='height: 100%; '>";
        echo "</div>\n";
        // 手数表示
        echo "<div id='tesuu' class='log'".
        "style='position:absolute; text-align: center; display:none; " .
        "left:" . $x0+$len*(4+1/6) . "px; " .
        "top:" . $y0+$len*(9+1/6) . "px; " . 
        "width:" . $len/3*2 . "px; " . 
        "height:" . $len/3*2 . "px;'>" .
        "<div><a>0</a></div></div>\n";
    }
    // 盤の角と、マスの大きさ
    $x0=200;
    $y0=100;
    $len=60;
    $komadai_w = $len*3;
    $komadai_h = $len*4;  
    // 上書き優先順位
    $z_index_window=6;
    $z_index_point=5;
    $z_index_line=4;
    $z_index_koma_counter=3;
    $z_index_koma=2;
    $z_index_masu=1;
    // 盤上初期化
    init();
?>
</body>
</html>