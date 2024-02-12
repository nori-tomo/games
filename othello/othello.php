<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <script>
        let n,Ox,Oy,len;
        let state={};
        let turn='b';
        let log={};
        let count=0;
        let end_count=-1;
        let count_b=0;
        let count_w=0;
        function init(n_,Ox_,Oy_,len_) {
            n=n_;Ox=Ox_;Oy=Oy_;len=len_;
            let e = document.getElementById("start_button");
            e.style.display = "none";
            e = document.getElementById("cnt_b");
            e.style.display = "";
            e = document.getElementById("cnt_w");
            e.style.display = "";
            for(let i=0; i<n; ++i) {
                for(let j=0; j<n; ++j) {
                    state[[i,j]]=-1;
                }
            }
            state[[n/2-1, n/2-1]]='w';
            state[[n/2, n/2]]='w';
            state[[n/2-1, n/2]]='b';
            state[[n/2, n/2-1]]='b';
            log[count++]={...state};
            show();
            candidate_show();
        }
        function put(x,y) {
            clear_candidate();
            update_state(x,y);
            log[count++]={...state};
            show();
            if(is_end()) {finish(); return;}
            turn_change();
            if(!candidate_show()) {
                turn_change();
                if(!candidate_show()) finish();
            }
        }
        function is_end() {
            for(let i=0; i<n; ++i) {
                for(let j=0; j<n; ++j) {
                    if(state[[i,j]]==-1) return 0;
                }
            }
            return 1;
        }
        function set_count(x) {
            function max(x,y) {if(x<y) return y; else return x;}
            function min(x,y) {if(x<y) return x; else return y;}
            if(x==-3) count=0;
            if(x==-2) count=max(count-5,0);
            if(x==-1) count=max(count-1,0);
            if(x==1) count=min(count+1,end_count);
            if(x==2) count=min(count+5,end_count);
            if(x==3) count=end_count;
        }
        function finish() {
            end_count = --count;
            document.querySelectorAll(".log").forEach(element => {
                element.style.display="";
            });
            document.getElementById("count").style.fontSize = "30px";
            document.getElementById("count").textContent = String(end_count);
        }
        function clear_candidate() {
            document.querySelectorAll(".block").forEach(element => {
                element.style.display="none";
            });
            document.querySelectorAll(".point").forEach(element => {
                element.style.display="none";
            });
        }
        function counter_show() {
            let r=0.3+0.2*2*count_w/(count_w+count_b);
            e = document.getElementById("cnt_w");
            e.innerHTML = "<font size='6'><b>"+String(count_w)+"</b></font>";
            e.style.lineHeight = String(len*r*2)+ "px";
            e.style.left = String(Ox-len*1.5-len*r)+ "px";
            e.style.top = String(Oy+len*n/2-len*r)+ "px";
            e.style.width = String(len*r*2)+ "px";
            e.style.height = String(len*r*2)+ "px";
            r = 1-r;
            e = document.getElementById("cnt_b");
            e.innerHTML = "<font color=white size='6'><b>"+String(count_b)+"</b></font>";
            e.style.lineHeight = String(len*r*2)+ "px";
            e.style.left = String(Ox+len*(n+1.5)-len*r)+ "px";
            e.style.top = String(Oy+len*n/2-len*r)+ "px";
            e.style.width = String(len*r*2)+ "px";
            e.style.height = String(len*r*2)+ "px";
        }
        function show_log() {
            let log_state = log[count];
            count_w=0; count_b=0;
            for(let i=0; i<n; ++i) {
                for(let j=0; j<n; ++j) {
                    let b_e = document.getElementById("stone"+String(i)+"_"+String(j)+"_b");
                    let w_e = document.getElementById("stone"+String(i)+"_"+String(j)+"_w");
                    if(log_state[[i,j]]=='b') {
                        b_e.style.display="";
                        w_e.style.display="none";
                        count_b++;
                    }
                    else if(log_state[[i,j]]=='w') {
                        b_e.style.display="none";
                        w_e.style.display="";
                        count_w++;
                    }
                    else {
                        b_e.style.display="none";
                        w_e.style.display="none";
                    }
                }
            }
            document.getElementById("count").textContent = String(count);
            counter_show();
        }
        function show() {
            count_w=0; count_b=0;
            for(let i=0; i<n; ++i) {
                for(let j=0; j<n; ++j) {
                    let b_e = document.getElementById("stone"+String(i)+"_"+String(j)+"_b");
                    let w_e = document.getElementById("stone"+String(i)+"_"+String(j)+"_w");
                    if(state[[i,j]]=='b') {
                        b_e.style.display="";
                        w_e.style.display="none";
                        count_b++;
                    }
                    else if(state[[i,j]]=='w') {
                        b_e.style.display="none";
                        w_e.style.display="";
                        count_w++;
                    }
                    else {
                        b_e.style.display="none";
                        w_e.style.display="none";
                    }
                }
            }
            counter_show();
        }
        function turn_change() {
            if(turn=='b') turn='w';
            else if(turn=='w') turn='b';
        }
        function update_state(x,y) {
            for(let dx=-1; dx<=1; ++dx) {
                for(let dy=-1; dy<=1; ++dy) {
                    if(dx==0 && dy==0) continue;
                    px=x+dx; py=y+dy;
                    if(!is_inside(px,py)) continue;
                    if(state[[px,py]]==turn) continue;
                    let is_flip_direction=0;
                    let flip_list=[];
                    while(is_inside(px,py)){
                        if(state[[px,py]]==-1) break;
                        if(state[[px,py]]==turn) {
                            is_flip_direction=1; break;
                        }
                        flip_list[flip_list.length]=[[px,py]];
                        px+=dx; py+=dy;
                    }
                    if(!is_flip_direction) continue;
                    for(let idx=0; idx<flip_list.length; ++idx) state[flip_list[idx]]=turn;
                }
            }
            state[[x,y]] = turn;
        }
        function is_inside(x,y) {
            if(x<0 || n<=x) return 0;
            if(y<0 || n<=y) return 0;
            return 1;
        }
        function is_able_to_put(x,y) {
            if(state[[x,y]]!=-1) return 0;
            let px,py;
            for(let dx=-1; dx<=1; ++dx) {
                for(let dy=-1; dy<=1; ++dy) {
                    if(dx==0 && dy==0) continue;
                    px=x+dx; py=y+dy;
                    if(!is_inside(px,py)) continue;
                    if(state[[px,py]]==turn) continue;
                    while(is_inside(px,py)){
                        if(state[[px,py]]==-1) break;
                        if(state[[px,py]]==turn) return 1;
                        px+=dx; py+=dy;
                    }
                }
            }
            return 0;
        }
        function get_candidate() {
            let candidate=[];
            for(let i=0; i<n; ++i) {
                for(let j=0; j<n; ++j) {
                    if(!is_able_to_put(i,j)) continue;
                    candidate[candidate.length]=[i,j];
                }
            }
            return candidate;
        }
        function candidate_show() {
            let candidate=get_candidate();
            if(candidate.length==0) return 0;
            for(let i=0; i<candidate.length; ++i) {
                let x = candidate[i][0];
                let y = candidate[i][1];
                let e = document.getElementById("point_"+turn+String(x)+"_"+String(y));
                e.style.display="";
                e = document.getElementById("block"+String(x)+"_"+String(y));
                e.style.display="";
            }
            return 1;
        }
    </script>
</head>
<body>
    <?php
        $n=8;
        $Ox=200;
        $Oy=100;
        $len=70;
        
        echo "<div id=board ".
        "style='position: absolute; background-color: green; ".
        "left:".$Ox-$len/2 ."px; top:".$Oy-$len/2 ."px;".
        "width:".$len*($n+1)."px; height:". $len*($n+1) ."px;".
        "'></div>\n";
        for($i=0; $i<$n+1; ++$i) {
            echo "<div id=l0_".$i." class=line ".
            "style='position: absolute; border-top: solid 4px; margin: -2px;".
            "left:".$Ox."px; top:".$Oy+$i*$len."px;".
            "width:".$len*$n."px; height:". 0 ."px;".
            "'></div>\n";
            echo "<div id=l1_".$i." class=line ".
            "style='position: absolute; border-left: solid 4px; margin: -2px;".
            "left:".$Ox+$i*$len."px; top:".$Oy."px;".
            "width:". 0 ."px; height:". $len*$n+4 ."px;".
            "'></div>\n";
        }
        for($i=0; $i<$n; ++$i) {
            for($j=0; $j<$n; ++$j) {
                echo "<div id=stone".$i."_".$j."_b class=stone ".
                "style='position: absolute; border-radius: 50%; background-color: #000; display: none;".
                "left:".$Ox+($i+0.05)*$len."px; top:".$Oy+($j+0.05)*$len."px;".
                "width:". $len*0.9 ."px; height:". $len*0.9 ."px;".
                "'></div>\n";
                echo "<div id=stone".$i."_".$j."_w class=stone ".
                "style='position: absolute; border-radius: 50%; background-color: #FFFFFF; display: none;".
                "left:".$Ox+($i+0.075)*$len."px; top:".$Oy+($j+0.075)*$len."px;".
                "width:". $len*0.85 ."px; height:". $len*0.85 ."px;".
                "'></div>\n";
                echo "<div id=point_b".$i."_".$j." class=point ".
                "onclick=\"put(".$i.','.$j.");\"".
                "style='position: absolute; border-radius: 50%; background-color: #00000099; display: none;".
                "left:".$Ox+($i+0.2)*$len."px; top:".$Oy+($j+0.2)*$len."px;".
                "width:". $len*0.6 ."px; height:". $len*0.6 ."px;".
                "'></div>\n";
                echo "<div id=point_w".$i."_".$j." class=point ".
                "onclick=\"put(".$i.','.$j.");\"".
                "style='position: absolute; border-radius: 50%; background-color: #FFFFFF99; display: none;".
                "left:".$Ox+($i+0.2)*$len."px; top:".$Oy+($j+0.2)*$len."px;".
                "width:". $len*0.6 ."px; height:". $len*0.6 ."px;".
                "'></div>\n";
                echo "<div id=block".$i."_".$j." class=block ".
                "onclick=\"put(".$i.','.$j.");\"".
                "style='position: absolute; background-color: #FFFFFF00; display: none;".
                "left:".$Ox+$i*$len."px; top:".$Oy+$j*$len."px;".
                "width:". $len ."px; height:". $len ."px;".
                "'></div>\n";
            }
        }
        // count動作ボタン
        $id_list = [['first', '端', 1, -3], ['pre_skip', 'スキップ', 1, -2], ['pre', '隣', 1, -1], 
                    ['next','隣',0, 1], ['next_skip', 'スキップ', 0, 2], ['last', '端', 0, 3]];
        for($i=0; $i<count($id_list); ++$i) {
            echo "<div id=".$id_list[$i][0]." " .
            "class='" . 'log' . "' " .
            "onclick='set_count(".$id_list[$i][3]."); show_log();'".
            "style='position:absolute; display:none; " .
            "left:" . $Ox+$len*($id_list[$i][3]+$n/2-0.5+1/6) . "px; " .
            "top:" . $Oy+$len*($n+0.5+1/6) . "px; " . 
            "width:" . $len/3*2 . "px; " . 
            "height:" . $len/3*2 . "px;'>" .
            "<img id='sente_img' src='image/".$id_list[$i][1].".png' style='height: 100%; ";
            if ($id_list[$i][2]==1) echo "transform: rotate(180deg);'>";
            else echo "'>";
            echo "</div>\n";
        }
        // count表示
        echo "<div id='count' class='log'".
        "style='position:absolute; text-align: center; display:none; " .
        "left:" . $Ox+$len*($n/2-0.5+1/6) . "px; " .
        "top:" . $Oy+$len*($n+0.5+1/6) . "px; " . 
        "width:" . $len/3*2 . "px; " . 
        "height:" . $len/3*2 . "px;'>" .
        "<div><a>0</a></div></div>\n";
        // counter
        $r=0.5;
        echo "<div id='cnt_b' class='counter'".
        "style='position:absolute; text-align: center; border-radius: 50%; background-color: #000; display:none; " .
        "line-height:" . $len*$r*2 . "px; " .
        "left:" . $Ox+$len*($n+1.5)-$len*$r . "px; " .
        "top:" . $Oy+$len*$n/2-$len*$r . "px; " . 
        "width:" . $len*$r*2 . "px; " . 
        "height:" . $len*$r*2 . "px;'>" .
        "<font color=white size='6'><b>2</b></font></div>\n";
        echo "<div id='cnt_w' class='counter'".
        "style='position:absolute; text-align: center; border: solid 3px; display:none; ".
        "border-radius: 50%; background-color: #FFFFFF00;" .
        "line-height:" . $len*$r*2 . "px; " .
        "left:" . $Ox-$len*1.5-$len*$r . "px; " .
        "top:" . $Oy+$len*$n/2-$len*$r . "px; " . 
        "width:" . $len*$r*2 . "px; " . 
        "height:" . $len*$r*2 . "px;'>" .
        "<font size='6'><b>2</b></font></div>\n";
        echo "<button id=start_button onclick=\"init(".$n.",".$Ox.",".$Oy.",".$len.");\">スタート</button>";
    ?>
</body>
</html>