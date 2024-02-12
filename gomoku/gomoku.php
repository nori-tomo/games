<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <script>
        let n,Ox,Oy,len;
        let state={};
        let turn='b';
        let log={};
        let count=-1;
        function init(n_,Ox_,Oy_,len_) {
            n=n_;Ox=Ox_;Oy=Oy_;len=len_;
            document.getElementById("start_button").style.display = "none";
            document.getElementById("rule").style.display = "none";
            for(let i=0; i<n; ++i) {
                for(let j=0; j<n; ++j) {
                    state[[i,j]]=-1;
                }
            }
            count=0;
            log[0]={...state};
            show();
            block_show();
        }
        function put(x,y) {
            if(count==-1) return;
            clear_block();
            state[[x,y]]=turn;
            count++;
            log[count]={...state};
            show();
            if(is_end() || shoubuari(x,y)) {finish(); return;}
            turn_change();
            block_show();
        }
        function is_end() {
            for(let i=0; i<n; ++i) {
                for(let j=0; j<n; ++j) {
                    if(state[[i,j]]==-1) return 0;
                }
            }
            return 1;
        }
        function set_count_and_state(x) {
            function max(x,y) {if(x<y) return y; else return x;}
            function min(x,y) {if(x<y) return x; else return y;}
            if(x==-1) count=0;
            if(x==0) count=max(count-5,0);
            if(x==1) count=max(count-1,0);
            if((count%2==0 && turn=='w')||(count%2==1 && turn=='b')) turn_change();
            state = {...log[count]};
        }
        function finish() {
            console.log("f");
            document.querySelectorAll(".block").forEach(element => {
                element.style.display="none";
            });
        }
        function clear_block() {
            document.querySelectorAll(".block").forEach(element => {
                element.style.display="none";
            });
            document.querySelectorAll(".point").forEach(element => {
                element.style.display="none";
            });
        }
        function show() {
            for(let i=0; i<n; ++i) {
                for(let j=0; j<n; ++j) {
                    let b_e = document.getElementById("stone"+String(i)+"_"+String(j)+"_b");
                    let w_e = document.getElementById("stone"+String(i)+"_"+String(j)+"_w");
                    let bl_e = document.getElementById("block"+String(i)+"_"+String(j));
                    if(state[[i,j]]=='b') {
                        b_e.style.display="";
                        w_e.style.display="none";
                        bl_e.style.display="none";
                    }
                    else if(state[[i,j]]=='w') {
                        b_e.style.display="none";
                        w_e.style.display="";
                        bl_e.style.display="none";
                    }
                    else {
                        b_e.style.display="none";
                        w_e.style.display="none";
                        bl_e.style.display="";
                    }
                }
            }
        }
        function turn_change() {
            if(turn=='b') {
                turn='w';
                document.querySelectorAll(".mark").forEach(element => {
                    element.style.backgroundColor = "white";
                });
            }
            else if(turn=='w') {
                turn='b';
                document.querySelectorAll(".mark").forEach(element => {
                    element.style.backgroundColor = "black";
                });
            }
        }
        function shoubuari(x,y) {
            for(let dx=0; dx<=1; ++dx) {
                for(let dy=-1; dy<=1; ++dy) {
                    if(dx==0 && dy==0) continue;
                    if(dx==0 && dy==-1) continue;
                    let cnt=1;
                    px=x+dx; py=y+dy;
                    while(is_inside(px,py)){
                        if(state[[px,py]]!=state[[x,y]]) break;
                        px+=dx; py+=dy; ++cnt;
                        if(cnt==5) return 1;
                    }
                    px=x-dx; py=y-dy;
                    while(is_inside(px,py)){
                        if(state[[px,py]]!=state[[x,y]]) break;
                        px-=dx; py-=dy; ++cnt;
                        if(cnt==5) return 1;
                    }
                }
            }
            return 0;
        }
        function is_inside(x,y) {
            if(x<0 || n<=x) return 0;
            if(y<0 || n<=y) return 0;
            return 1;
        }
        function is_unable_to_put(x,y) {
            let px,py,cnt,bool, cnt_free3=0;
            state[[x,y]]=turn;// 仮に置く
            for(let dx=0; dx<=1; ++dx) {
                for(let dy=-1; dy<=1; ++dy) {
                    if(dx==0 && dy==0) continue;
                    if(dx==0 && dy==-1) continue;//　四方向に限定
                    let bool_5=0;
                    for(let a=0; a<5; ++a) {
                        cnt=0;
                        for(let b=0; b<5; ++b) {
                            px=x+dx*(a-b); py=y+dy*(a-b);
                            if(!is_inside(px,py)) break;
                            if(state[[px,py]]==turn) ++cnt;
                        }
                        if(cnt==5) bool_5=1;
                    }
                    if(bool_5) {state[[x,y]]=-1; return 0;} // 置ける
                    let bool_ren_4=0; bool=1;
                    for(let a=0; a<4; ++a) {
                        cnt=0;
                        for(let b=0; b<4; ++b) {
                            px=x+dx*(a-b); py=y+dy*(a-b);
                            if(!is_inside(px,py)) bool=0;
                            if(state[[px,py]]==turn) ++cnt;
                        }
                        if(cnt==4 && bool) bool_ren_4=1;
                    }
                    if(bool_ren_4) {state[[x,y]]=-1; return 0;} // 置ける
                    let bool_tobi_4=0; bool=1;
                    for(let a=0; a<5; ++a) {
                        cnt=0;
                        for(let b=0; b<5; ++b) {
                            px=x+dx*(a-b); py=y+dy*(a-b);
                            if(!is_inside(px,py)) bool=0;
                            if(state[[px,py]]==turn) ++cnt;
                            else if(state[[px,py]]!=-1) bool=0; // 間に相手の石
                        }
                        if(cnt==4 && bool) bool_tobi_4=1;
                    }
                    if(bool_tobi_4) {state[[x,y]]=-1; return 0;} // 置ける

                    let bool_tobi_free3=0; bool=1;
                    for(let a=0; a<4; ++a) {
                        // 端が何も置かれていないか
                        px=x+dx*(a+1); py=y+dy*(a+1);
                        if(!is_inside(px,py)) continue;
                        if(state[[px,py]]!=-1) continue;
                        px=x+dx*(a-4); py=y+dy*(a-4);
                        if(!is_inside(px,py)) continue;
                        if(state[[px,py]]!=-1) continue;
                        cnt=0;
                        for(let b=0; b<4; ++b) {
                            px=x+dx*(a-b); py=y+dy*(a-b);
                            if(!is_inside(px,py)) bool=0;
                            if(state[[px,py]]==turn) ++cnt;
                            else if(state[[px,py]]!=-1) bool=0; // 間に相手の石
                        }
                        if(cnt==3 && bool) bool_tobi_free3=1;
                    }
                    if(bool_tobi_free3) {cnt_free3++; continue;} // 3あり
                    let bool_free3=0; bool=1;
                    for(let a=0; a<3; ++a) {
                        // 端が何も置かれていないか
                        px=x+dx*(a+1); py=y+dy*(a+1);
                        if(!is_inside(px,py)) continue;
                        if(state[[px,py]]!=-1) continue;
                        px=x+dx*(a-3); py=y+dy*(a-3);
                        if(!is_inside(px,py)) continue;
                        if(state[[px,py]]!=-1) continue;
                        cnt=0;
                        for(let b=0; b<3; ++b) {
                            px=x+dx*(a-b); py=y+dy*(a-b);
                            if(!is_inside(px,py)) bool=0;
                            if(state[[px,py]]==turn) ++cnt;
                        }
                        if(cnt==3 && bool) bool_free3=1;
                    }
                    if(bool_free3) cnt_free3++; // 3あり
                }
            }
            state[[x,y]]=-1;// 戻す
            return cnt_free3>1;
        }
        function block_show() {
            let e;
            for(let i=0; i<n; ++i) {
                for(let j=0; j<n; ++j) {
                    if(state[[i,j]]!=-1) continue;
                    if(is_unable_to_put(i,j)) {
                        e = document.getElementById("block"+String(i)+"_"+String(j));
                        e.style.display="none";
                        e = document.getElementById("point"+String(i)+"_"+String(j));
                        e.style.display="";
                    }
                    else {
                        e = document.getElementById("block"+String(i)+"_"+String(j));
                        e.style.display="";
                        e = document.getElementById("point"+String(i)+"_"+String(j));
                        e.style.display="none";
                    }
                }
            }
        }
    </script>
</head>
<body>
    <?php
        $n=15;
        $Ox=200;
        $Oy=100;
        $len=40;
        
        echo "<div id=board ".
        "style='position: absolute; background-color: burlywood; ".
        "left:".$Ox-$len*1.5 ."px; top:".$Oy-$len*1.5 ."px;".
        "width:".$len*($n+2)."px; height:". $len*($n+2) ."px;".
        "'></div>\n";
        for($i=0; $i<$n; ++$i) {
            echo "<div id=l0_".$i." class=line ".
            "style='position: absolute; border-top: solid 1px; margin: 0px;".
            "left:".$Ox."px; top:".$Oy+$i*$len."px;".
            "width:".$len*($n-1)."px; height:". 0 ."px;".
            "'></div>\n";
            echo "<div id=l1_".$i." class=line ".
            "style='position: absolute; border-left: solid 1px; margin: 0px;".
            "left:".$Ox+$i*$len."px; top:".$Oy."px;".
            "width:". 0 ."px; height:". $len*($n-1)+1 ."px;".
            "'></div>\n";
        }
        for($i=0; $i<$n; ++$i) {
            for($j=0; $j<$n; ++$j) {
                echo "<div id=stone".$i."_".$j."_b class=stone ".
                "style='position: absolute; border-radius: 50%; background-color: #000; display: none;".
                "left:".$Ox+($i+0.1-0.5)*$len."px; top:".$Oy+($j+0.1-0.5)*$len."px;".
                "width:". $len*0.8 ."px; height:". $len*0.8 ."px;".
                "'></div>\n";
                echo "<div id=stone".$i."_".$j."_w class=stone ".
                "style='position: absolute; border-radius: 50%; background-color: #FFFFFF; display: none;".
                "left:".$Ox+($i+0.125-0.5)*$len."px; top:".$Oy+($j+0.125-0.5)*$len."px;".
                "width:". $len*0.75 ."px; height:". $len*0.75 ."px;".
                "'></div>\n";
                echo "<div id=point".$i."_".$j." class=point ".
                "style='position: absolute; border-radius: 50%; background-color: silver; display: none;".
                "left:".$Ox+($i+0.2-0.5)*$len."px; top:".$Oy+($j+0.2-0.5)*$len."px;".
                "width:". $len*0.6 ."px; height:". $len*0.6 ."px;".
                "'></div>\n";
                echo "<div id=block".$i."_".$j." class=block ".
                "onclick=\"put(".$i.','.$j.");\"".
                "style='position: absolute; background-color: #00000000; display: ;".
                "left:".$Ox+($i-0.5)*$len."px; top:".$Oy+($j-0.5)*$len."px;".
                "width:". $len ."px; height:". $len ."px;".
                "'></div>\n";
            }
        }
        // count動作ボタン
        $id_list = [['first', '端', 1, -1], ['pre_skip', 'スキップ', 1, 0], ['pre', '隣', 1, 1]];
        for($i=0; $i<count($id_list); ++$i) {
            echo "<div id=".$id_list[$i][0]." " .
            "class='" . 'log' . "' " .
            "onclick='set_count_and_state(".$id_list[$i][3]."); show(); block_show();'".
            "style='position:absolute; display:; " .
            "left:" . $Ox+$len*($id_list[$i][3]+($n-1)/2-0.5+1/6) . "px; " .
            "top:" . $Oy+$len*(($n-1)+1.5+1/6) . "px; " . 
            "width:" . $len/3*2 . "px; " . 
            "height:" . $len/3*2 . "px;'>" .
            "<img id='sente_img' src='image/".$id_list[$i][1].".png' style='height: 100%; ";
            if ($id_list[$i][2]==1) echo "transform: rotate(180deg);'>";
            else echo "'>";
            echo "</div>\n";
        }
        // turn_mark
        $r=0.2; $d=$len*(($n-1)/2-0.5+1.25);
        for($z=-$len/2; $z<=$len/2; $z+=$len/2) {
            for($i=0; $i<4; ++$i) {
                if($i==0) {$x=$z;$y=$d;}
                if($i==1) {$x=$z;$y=-$d;}
                if($i==2) {$x=$d;$y=$z;}
                if($i==3) {$x=-$d;$y=$z;}
            
                echo "<div id='turn' class='mark' ".
                "style='position:absolute; border-radius: 50%; background-color: black; display:; " .
                "left:" . $Ox+$len*(($n-1)/2-0.5)+$x+$len*(1-$r)/2 . "px; " .
                "top:" . $Oy+$len*(($n-1)/2-0.5)+$y+$len*(1-$r)/2 . "px; " . 
                "width:" . $len*$r . "px; " . 
                "height:" . $len*$r . "px;'>" .
                "</div>\n";
            }
        }
        // ルール確認
        echo "<p id=rule>白黒三三なし</p>";
        echo "<button id=start_button onclick=\"init(".$n.",".$Ox.",".$Oy.",".$len.");\">スタート</button>";
    ?>
</body>
</html>