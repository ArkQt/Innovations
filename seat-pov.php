<?php
/**
 * seat-pov.php — Ticketix Seat POV Feature
 *
 * USAGE: Add this one line BEFORE </body> in seat-reservation.php:
 *   <?php include 'seat-pov.php'; ?>
 *
 * That's it. The "👁 View from Seat" button is auto-injected next to
 * your existing Proceed button. No other PHP changes needed.
 */
?>

<!-- ═══════════════════════════════════════════════════
     POV MODAL HTML
═══════════════════════════════════════════════════ -->
<div id="pov-overlay" class="pov-overlay" role="dialog" aria-modal="true" aria-label="Seat Point of View">
    <div class="pov-modal">

        <div class="pov-header">
            <div class="pov-header-left">
                <span class="pov-eye-icon">👁</span>
                <div>
                    <h2 class="pov-title">View From Seat</h2>
                    <p class="pov-subtitle" id="pov-seat-label">Select a seat to preview</p>
                </div>
            </div>
            <button class="pov-close-btn" id="pov-close-btn" aria-label="Close">✕</button>
        </div>

        <div class="pov-seat-nav" id="pov-seat-nav">
            <button class="pov-nav-btn" id="pov-prev-btn">&#8249;</button>
            <span class="pov-nav-label" id="pov-nav-label">Seat 1 of 1</span>
            <button class="pov-nav-btn" id="pov-next-btn">&#8250;</button>
        </div>

        <div class="pov-viewport-wrapper">
            <canvas id="pov-canvas" class="pov-canvas" width="700" height="340"></canvas>
            <div class="pov-scanlines"></div>
            <div class="pov-vignette"></div>
        </div>

        <div class="pov-stats">
            <div class="pov-stat">
                <span class="pov-stat-icon">&#128207;</span>
                <div>
                    <div class="pov-stat-label">Distance</div>
                    <div class="pov-stat-value" id="pov-distance">&#8212;</div>
                </div>
            </div>
            <div class="pov-stat">
                <span class="pov-stat-icon">&#128208;</span>
                <div>
                    <div class="pov-stat-label">Viewing Angle</div>
                    <div class="pov-stat-value" id="pov-angle">&#8212;</div>
                </div>
            </div>
            <div class="pov-stat">
                <span class="pov-stat-icon">&#127916;</span>
                <div>
                    <div class="pov-stat-label">Screen Coverage</div>
                    <div class="pov-stat-value" id="pov-coverage">&#8212;</div>
                </div>
            </div>
            <div class="pov-stat">
                <span class="pov-stat-icon">&#11088;</span>
                <div>
                    <div class="pov-stat-label">View Quality</div>
                    <div class="pov-stat-value pov-quality-value" id="pov-quality">&#8212;</div>
                </div>
            </div>
        </div>

        <div class="pov-quality-bar-wrapper">
            <div class="pov-quality-bar-track">
                <div class="pov-quality-bar-fill" id="pov-quality-bar"></div>
            </div>
            <div class="pov-quality-labels">
                <span>Poor</span><span>Fair</span><span>Good</span><span>Great</span><span>Prime</span>
            </div>
        </div>

    </div>
</div>

<!-- ═══════════════════════════════════════════════════
     POV STYLES
═══════════════════════════════════════════════════ -->
<style>
/* Trigger Button */
.pov-trigger-btn {
    background: transparent;
    border: 2px solid #00BFFF;
    color: #00BFFF;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 13px;
    border-radius: 40px;
    padding: 10px 22px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    letter-spacing: 0.3px;
    margin-right: 10px;
}
.pov-trigger-btn:hover:not(:disabled) {
    background: rgba(0,191,255,0.15);
    box-shadow: 0 0 18px rgba(0,191,255,0.4);
    transform: translateY(-1px);
}
.pov-trigger-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
    border-color: rgba(0,191,255,0.4);
}
.pov-trigger-btn .pov-pulse {
    width: 8px;
    height: 8px;
    background: #00BFFF;
    border-radius: 50%;
    display: inline-block;
    animation: povPulse 1.4s ease-in-out infinite;
}
@keyframes povPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: 0.4; transform: scale(0.6); }
}

/* Overlay */
.pov-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.88);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.pov-overlay.active {
    display: flex;
    animation: povFadeIn 0.25s ease;
}
@keyframes povFadeIn { from { opacity: 0; } to { opacity: 1; } }

/* Modal */
.pov-modal {
    background: linear-gradient(145deg, #0d1f2d, #091520);
    border: 1.5px solid rgba(0,191,255,0.25);
    border-radius: 18px;
    padding: 24px;
    width: 100%;
    max-width: 760px;
    box-shadow: 0 0 60px rgba(0,191,255,0.08), 0 30px 80px rgba(0,0,0,0.6);
    animation: povSlideUp 0.3s cubic-bezier(0.34,1.56,0.64,1);
    position: relative;
}
@keyframes povSlideUp {
    from { transform: translateY(30px) scale(0.97); opacity: 0; }
    to   { transform: translateY(0) scale(1); opacity: 1; }
}

/* Header */
.pov-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.pov-header-left { display: flex; align-items: center; gap: 12px; }
.pov-eye-icon { font-size: 26px; filter: drop-shadow(0 0 8px rgba(0,191,255,0.6)); }
.pov-title { font-family: 'Poppins', sans-serif; font-size: 18px; font-weight: 700; color: #fff; margin: 0; }
.pov-subtitle { font-size: 12px; color: #00BFFF; margin: 0; font-family: 'Poppins', sans-serif; letter-spacing: 0.5px; }
.pov-close-btn {
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.12);
    color: #aaa;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.pov-close-btn:hover { background: rgba(255,107,107,0.2); border-color: #FF6B6B; color: #FF6B6B; }

/* Seat Navigator */
.pov-seat-nav {
    display: none;
    align-items: center;
    justify-content: center;
    gap: 14px;
    margin-bottom: 10px;
}
.pov-seat-nav.visible { display: flex; }
.pov-nav-btn {
    background: rgba(0,191,255,0.12);
    border: 1px solid rgba(0,191,255,0.3);
    color: #00BFFF;
    border-radius: 8px;
    width: 32px;
    height: 32px;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.pov-nav-btn:hover { background: rgba(0,191,255,0.25); box-shadow: 0 0 10px rgba(0,191,255,0.3); }
.pov-nav-label { font-size: 13px; color: #ccc; font-family: 'Poppins', sans-serif; min-width: 80px; text-align: center; }

/* Viewport */
.pov-viewport-wrapper {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid rgba(0,191,255,0.15);
    margin-bottom: 16px;
    background: #000;
}
.pov-canvas { display: block; width: 100%; height: auto; }
.pov-scanlines {
    position: absolute;
    inset: 0;
    background: repeating-linear-gradient(to bottom, transparent 0px, transparent 3px, rgba(0,0,0,0.08) 3px, rgba(0,0,0,0.08) 4px);
    pointer-events: none;
    z-index: 2;
}
.pov-vignette {
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at center, transparent 55%, rgba(0,0,0,0.65) 100%);
    pointer-events: none;
    z-index: 3;
}

/* Stats */
.pov-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 14px; }
.pov-stat {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(0,191,255,0.12);
    border-radius: 10px;
    padding: 10px 12px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.pov-stat-icon { font-size: 18px; }
.pov-stat-label { font-size: 10px; color: #6b8fa8; font-family: 'Poppins', sans-serif; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
.pov-stat-value { font-size: 13px; font-weight: 700; color: #fff; font-family: 'Poppins', sans-serif; }
.pov-quality-value { color: #00BFFF; }

/* Quality Bar */
.pov-quality-bar-track { height: 6px; background: rgba(255,255,255,0.08); border-radius: 99px; overflow: hidden; margin-bottom: 6px; }
.pov-quality-bar-fill { height: 100%; border-radius: 99px; width: 0%; transition: width 0.6s cubic-bezier(0.34,1.2,0.64,1), background 0.4s ease; }
.pov-quality-labels { display: flex; justify-content: space-between; font-size: 10px; color: #4a6a7a; font-family: 'Poppins', sans-serif; letter-spacing: 0.3px; }

@media (max-width: 600px) {
    .pov-stats { grid-template-columns: repeat(2, 1fr); }
    .pov-modal { padding: 16px; }
}
</style>

<!-- ═══════════════════════════════════════════════════
     POV JAVASCRIPT
═══════════════════════════════════════════════════ -->
<script>
(function () {
    // ── Constants ────────────────────────────────────────────
    var ROWS        = ['A','B','C','D','E','F','G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
    var MAX_OFFSET = 8.5;
    var ROW_CENTERS = {};

    // ── DOM refs ─────────────────────────────────────────────
    var overlay      = document.getElementById('pov-overlay');
    var closeBtn     = document.getElementById('pov-close-btn');
    var canvas       = document.getElementById('pov-canvas');
    var ctx          = canvas.getContext('2d');
    var seatLabel    = document.getElementById('pov-seat-label');
    var seatNav      = document.getElementById('pov-seat-nav');
    var navLabel     = document.getElementById('pov-nav-label');
    var prevBtn      = document.getElementById('pov-prev-btn');
    var nextBtn      = document.getElementById('pov-next-btn');
    var statDistance = document.getElementById('pov-distance');
    var statAngle    = document.getElementById('pov-angle');
    var statCoverage = document.getElementById('pov-coverage');
    var statQuality  = document.getElementById('pov-quality');
    var qualityBar   = document.getElementById('pov-quality-bar');

    // ── State ─────────────────────────────────────────────────
    var currentSeatIndex = 0;
    var seatList  = [];
    var animFrame = null;
    var animTick  = 0;

    // ── Auto-inject trigger button next to Proceed ───────────
    var proceedBtn = document.getElementById('proceed-btn');
    if (proceedBtn && !document.getElementById('pov-trigger-btn')) {
        var btn = document.createElement('button');
        btn.type      = 'button';
        btn.id        = 'pov-trigger-btn';
        btn.className = 'pov-trigger-btn';
        btn.disabled  = true;
        btn.innerHTML = '<span class="pov-pulse"></span> &#128065; View from Seat';
        proceedBtn.parentNode.insertBefore(btn, proceedBtn);
    }

    // ── Seat ID parser ────────────────────────────────────────
    function parseSeat(seatId) {
        var m = seatId.match(/^([A-Z])-?(\d+)$/i);
        return m ? { row: m[1].toUpperCase(), col: parseInt(m[2], 10) } : null;
    }

    // ── POV calculation ───────────────────────────────────────
    function getSeatPOV(seatId) {
        if (Object.keys(ROW_CENTERS).length === 0) {
            var globalMaxCol = 0;
            var seats = document.querySelectorAll('.seat');
            seats.forEach(function(s) {
                var d = s.getAttribute('data-seat');
                if (d) {
                    var m = d.match(/^([A-Z])-?(\d+)$/i);
                    if (m) {
                        var rowLetter = m[1].toUpperCase();
                        var colNum = parseInt(m[2], 10);
                        if (!ROW_CENTERS[rowLetter] || colNum > ROW_CENTERS[rowLetter].maxCol) {
                            ROW_CENTERS[rowLetter] = { maxCol: colNum };
                        }
                        if (colNum > globalMaxCol) globalMaxCol = colNum;
                    }
                }
            });
            // Calculate center point for each specific row based on its max columns length
            Object.keys(ROW_CENTERS).forEach(function(r) {
                ROW_CENTERS[r].center = (ROW_CENTERS[r].maxCol + 1) / 2;
            });
            MAX_OFFSET = Math.max(8.5, (globalMaxCol - 1) / 2);
        }

        var parsed = parseSeat(seatId);
        if (!parsed) return null;
        var row = parsed.row;
        var col = parsed.col;

        var rowIndex      = ROWS.indexOf(row);        
        if (rowIndex === -1) rowIndex = row.charCodeAt(0) - 65; // fallback
        var distanceUnits = rowIndex + 1;             

        // Get the specific true center for THIS row
        var rowCenter = ROW_CENTERS[row] ? ROW_CENTERS[row].center : 9.5;
        var offsetX   = col - rowCenter;        

        // Normalised skew: -1 (seat hard left) … 0 (centre) … +1 (seat hard right)
        // Adjust the scale to match the row's physical offset
        var maxOffsetForRow = ROW_CENTERS[row] ? (ROW_CENTERS[row].maxCol - 1) / 2 : MAX_OFFSET;
        // Map offset smoothly based on the row's own physical width
        var skew = maxOffsetForRow > 0 ? (offsetX / maxOffsetForRow) : 0;
        // If a row is very narrow (like row A only has 9 seats but G has 18), 
        // the "edges" of row A shouldn't skew as extremely as the "edges" of row G.
        var rowWidthRatio = maxOffsetForRow / MAX_OFFSET;
        skew = skew * rowWidthRatio;
        
        skew = Math.max(-1.5, Math.min(1.5, skew));
        // Screen size shrinks for back rows
        var distScale = Math.max(0.35, 1.0 - rowIndex * 0.08);       

        // Viewing angle
        var angleRad = Math.atan2(Math.abs(offsetX), distanceUnits * 2.2);
        var angleDeg = Math.round(angleRad * 180 / Math.PI);

        // Where the screen appears from this seat:
        //   Left seat  (offsetX < 0) -> screen appears to the RIGHT
        //   Right seat (offsetX > 0) -> screen appears to the LEFT
        var sideLabel = offsetX < -0.5 ? 'Right' : (offsetX > 0.5 ? 'Left' : 'Center');

        // Quality
        var qualityScore, qualityLabel, qualityColor;
        var isFront  = rowIndex <= 1;
        var isBack   = rowIndex >= 5;
        var isSide   = angleDeg > 25;
        var isCenter = angleDeg < 8;
        var isMid    = rowIndex >= 2 && rowIndex <= 4;

        if      (isMid && isCenter)       { qualityScore=5; qualityLabel='Prime';     qualityColor='#00BFFF'; }
        else if (isMid && angleDeg < 18)  { qualityScore=4; qualityLabel='Great';     qualityColor='#6BCB77'; }
        else if (isMid && !isSide)        { qualityScore=3; qualityLabel='Good';      qualityColor='#FFD93D'; }
        else if (isFront && isCenter)     { qualityScore=3; qualityLabel='Too Close'; qualityColor='#FFD93D'; }
        else if (isBack  && isCenter)     { qualityScore=3; qualityLabel='Far Back';  qualityColor='#FFD93D'; }
        else if (isSide  && isFront)      { qualityScore=1; qualityLabel='Poor';      qualityColor='#FF6B6B'; }
        else if (isSide)                  { qualityScore=2; qualityLabel='Angled';    qualityColor='#FF9F43'; }
        else                              { qualityScore=3; qualityLabel='Fair';      qualityColor='#FFD93D'; }

        var distanceMap = ['Very Close','Very Close','Close','Mid','Mid','Far','Far'];
        var distanceFt  = Math.round(distanceUnits * 5) + ' ft (' + distanceMap[rowIndex] + ')';
        var screenCoverage = Math.min(100, Math.round((1 - Math.abs(skew) * 0.25) * distScale * 110));

        return {
            row: row, col: col, rowIndex: rowIndex, seatId: seatId,
            distanceFt: distanceFt, angleDeg: angleDeg, sideLabel: sideLabel,
            skew: skew, distScale: distScale, screenCoverage: screenCoverage,
            qualityScore: qualityScore, qualityLabel: qualityLabel, qualityColor: qualityColor
        };
    }

    // Expose getSeatPOV globally so seat-reservation.php can use it for pricing
    window.getSeatPOV = getSeatPOV;

    // ── Canvas helper: rounded rectangle path ─────────────────
    function roundRect(c, x, y, w, h, r) {
        c.beginPath();
        c.moveTo(x+r, y);
        c.lineTo(x+w-r, y);
        c.quadraticCurveTo(x+w, y, x+w, y+r);
        c.lineTo(x+w, y+h-r);
        c.quadraticCurveTo(x+w, y+h, x+w-r, y+h);
        c.lineTo(x+r, y+h);
        c.quadraticCurveTo(x, y+h, x, y+h-r);
        c.lineTo(x, y+r);
        c.quadraticCurveTo(x, y, x+r, y);
        c.closePath();
    }

    // ── Main canvas render ────────────────────────────────────
    function drawPOV(pov) {
        var W = canvas.width;   // 700
        var H = canvas.height;  // 340
        animTick++;

        ctx.clearRect(0, 0, W, H);

        // Background gradient
        var bg = ctx.createRadialGradient(W/2, H*0.5, 10, W/2, H*0.5, W*0.85);
        bg.addColorStop(0, '#0e1c26');
        bg.addColorStop(1, '#040a0e');
        ctx.fillStyle = bg;
        ctx.fillRect(0, 0, W, H);

        // ── Trapezoid geometry ─────────────────────────────────
        //
        //   Centre seat  (skew = 0):  equal heights → rectangle
        //   Left seat    (skew < 0):  right edge taller, left edge shorter
        //   Right seat   (skew > 0):  left edge taller, right edge shorter
        //
        //   Centre:   ┌──────────────────┐
        //   Left:     /──────────────────|   (right side tall)
        //   Right:    |──────────────────\   (left side tall)

        var sk = pov.skew;       // bounded near -1 … +1
        var ds = pov.distScale;  

        // Make the screen fully visible AND represent the real POV location
        var baseScreenW = W * 0.65; // Slightly narrower so panning is visible
        var SCREEN_W = baseScreenW * (0.6 + 0.4 * ds);
        
        // Pan the screen to simulate turning head 
        // If we sit left (skew < 0), the screen must appear to the right (panX > 0)
        // If we sit right (skew > 0), the screen must appear to the left (panX < 0)
        var maxPan = (W - SCREEN_W) / 2 - 25; // Keep slightly away from edge
        if (maxPan < 0) maxPan = 0;
        var panX = -sk * maxPan;

        var x0 = (W / 2) + panX - (SCREEN_W / 2);
        var x1 = (W / 2) + panX + (SCREEN_W / 2);

        var MID_Y    = H * 0.36;
        var BASE_H   = 120 * ds; 

        var nearH = BASE_H * (1.0 + Math.abs(sk) * 0.55);
        var farH  = BASE_H * (1.0 - Math.abs(sk) * 0.45);

        // Keep heights strictly fully within screen to prevent clipping at corners
        var maxAllowedH = (MID_Y - 15) * 2; // don't clip top edge
        if (nearH > maxAllowedH) {
            var sc = maxAllowedH / nearH;
            nearH *= sc;
            farH  *= sc;
        }

        var leftH, rightH;
        if (sk <= 0) {
            leftH  = nearH;   // seat left → left side of screen is near (taller)
            rightH = farH;
        } else {
            leftH  = farH;  // seat right → right side of screen is near (taller)
            rightH = nearH;
        }

        var tl = { x: x0, y: MID_Y - leftH  / 2 };
        var tr = { x: x1, y: MID_Y - rightH / 2 };
        var br = { x: x1, y: MID_Y + rightH / 2 };
        var bl = { x: x0, y: MID_Y + leftH  / 2 };

        var trapCX = (x0 + x1) / 2;
        var trapCY = MID_Y;

        // Save panX for use by silhouettes
        pov.panX = panX;

        // ── Perspective grid ──────────────────────────────────
        ctx.lineWidth = 1;
        for (var i = 0; i <= 14; i++) {
            var gx = (i / 14) * W;
            ctx.strokeStyle = 'rgba(0,191,255,0.04)';
            ctx.beginPath();
            ctx.moveTo(gx, H);
            ctx.lineTo(trapCX, trapCY);
            ctx.stroke();
        }
        for (var d = 1; d <= 5; d++) {
            var gt  = d / 6;
            var gy  = H - (H - trapCY) * (1 - gt * gt);
            var ghw = W * 0.52 * (1 - gt * 0.3);
            ctx.strokeStyle = 'rgba(0,191,255,' + (0.02 + gt * 0.03) + ')';
            ctx.beginPath();
            ctx.moveTo(trapCX - ghw, gy);
            ctx.lineTo(trapCX + ghw, gy);
            ctx.stroke();
        }

        // ── Screen glow ───────────────────────────────────────
        var halo = ctx.createRadialGradient(trapCX, trapCY, 10, trapCX, trapCY, SCREEN_W * 0.7);
        halo.addColorStop(0, 'rgba(0,191,255,0.12)');
        halo.addColorStop(1, 'rgba(0,0,0,0)');
        ctx.fillStyle = halo;
        ctx.fillRect(0, 0, W, H);

        // ── Clip to trapezoid and fill ────────────────────────
        ctx.save();
        ctx.beginPath();
        ctx.moveTo(tl.x, tl.y);
        ctx.lineTo(tr.x, tr.y);
        ctx.lineTo(br.x, br.y);
        ctx.lineTo(bl.x, bl.y);
        ctx.closePath();
        ctx.clip();

        var t = animTick * 0.008;
        var mg = ctx.createLinearGradient(x0, 0, x1, 0);
        mg.addColorStop(0,    'hsl(' + (210+Math.sin(t)*15)    + ',55%,' + (18+Math.sin(t*0.7)*4)  + '%)');
        mg.addColorStop(0.30, 'hsl(' + (190+Math.cos(t*0.9)*20)+ ',45%,' + (28+Math.cos(t)*6)      + '%)');
        mg.addColorStop(0.65, 'hsl(' + (230+Math.sin(t*1.1)*12)+ ',50%,' + (20+Math.sin(t*0.5)*4)  + '%)');
        mg.addColorStop(1,    'hsl(' + (200+Math.cos(t*0.8)*18)+ ',45%,' + (15+Math.cos(t*0.6)*4)  + '%)');
        ctx.fillStyle = mg;
        var fillTop = Math.min(tl.y, tr.y) - 2;
        var fillBot = Math.max(bl.y, br.y) + 2;
        ctx.fillRect(x0 - 2, fillTop, SCREEN_W + 4, fillBot - fillTop);

        for (var sl = fillTop; sl < fillBot; sl += 5) {
            ctx.fillStyle = 'rgba(0,0,0,0.10)';
            ctx.fillRect(x0 - 2, sl, SCREEN_W + 4, 2);
        }
        ctx.restore();

        // ── Trapezoid border ──────────────────────────────────
        ctx.beginPath();
        ctx.moveTo(tl.x, tl.y);
        ctx.lineTo(tr.x, tr.y);
        ctx.lineTo(br.x, br.y);
        ctx.lineTo(bl.x, bl.y);
        ctx.closePath();
        ctx.strokeStyle = 'rgba(0,191,255,0.60)';
        ctx.lineWidth   = 2.5;
        ctx.stroke();
        ctx.strokeStyle = 'rgba(0,191,255,0.12)';
        ctx.lineWidth   = 7;
        ctx.stroke();

        // ── SCREEN label ──────────────────────────────────────
        ctx.save();
        ctx.font      = 'bold ' + Math.round(14 * ds) + 'px Poppins, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillStyle = 'rgba(255,255,255,0.28)';
        ctx.fillText('SCREEN', trapCX, trapCY + 5);
        ctx.restore();

        // ── Seat silhouettes (rows in front) ──────────────────
        var maxSilRows = Math.min(pov.rowIndex, 4);
        var seatPanModifier = pov.panX || 0;
        
        for (var r = 0; r < maxSilRows; r++) {
            var depthT    = (r + 1) / (maxSilRows + 1);
            var rowY      = H * 0.71 - r * H * 0.085;
            var rowAlpha  = 0.07 + depthT * 0.11;
            var rowWidth  = W * (0.28 + depthT * 0.52);
            
            // Pan silhouettes opposite to the screen to simulate looking laterally
            var seatPanX = -seatPanModifier * (0.5 + depthT * 0.25);
            var rowStartX = (W - rowWidth) / 2 + seatPanX;
            var seatCount = Math.round(6 + depthT * 8);

            for (var c = 0; c < seatCount; c++) {
                var sx = rowStartX + (c / (seatCount - 1)) * rowWidth;
                var sh = 11 + depthT * 15;
                var sw = rowWidth / (seatCount * 1.35);
                ctx.fillStyle = 'rgba(18,42,60,' + (rowAlpha + 0.12) + ')';
                roundRect(ctx, sx - sw/2, rowY, sw, sh, 3);
                ctx.fill();
                ctx.fillStyle = 'rgba(28,56,80,' + rowAlpha + ')';
                ctx.fillRect(sx - sw*0.33, rowY - sh*0.28, sw*0.66, sh*0.26);
            }
        }

        // ── Your Seat marker ──────────────────────────────────
        var pulse = 0.82 + Math.sin(animTick * 0.07) * 0.18;
        var myY   = H - 50;

        ctx.strokeStyle = 'rgba(0,191,255,' + pulse + ')';
        ctx.lineWidth   = 2;
        roundRect(ctx, W/2 - 15, myY, 30, 23, 4);
        ctx.stroke();
        ctx.fillStyle = 'rgba(0,191,255,' + (0.15 * pulse) + ')';
        roundRect(ctx, W/2 - 15, myY, 30, 23, 4);
        ctx.fill();
        ctx.strokeStyle = 'rgba(0,191,255,' + (pulse * 0.65) + ')';
        ctx.lineWidth   = 1.5;
        ctx.strokeRect(W/2 - 9, myY - 8, 18, 9);

        ctx.font      = 'bold 11px Poppins, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillStyle = 'rgba(0,191,255,' + (0.75 + Math.sin(animTick*0.07)*0.25) + ')';
        ctx.fillText('YOUR SEAT', W/2, H - 14);

        // ── Seat ID badge (top-left) ──────────────────────────
        ctx.fillStyle   = 'rgba(0,191,255,0.10)';
        roundRect(ctx, 12, 12, 88, 28, 6);
        ctx.fill();
        ctx.strokeStyle = 'rgba(0,191,255,0.30)';
        ctx.lineWidth   = 1;
        roundRect(ctx, 12, 12, 88, 28, 6);
        ctx.stroke();
        ctx.fillStyle = '#00BFFF';
        ctx.font      = 'bold 13px Poppins, sans-serif';
        ctx.textAlign = 'left';
        ctx.fillText('Seat ' + pov.seatId, 20, 31);

        // ── Angle badge (top-right) ───────────────────────────
        if (pov.angleDeg > 4) {
            var badgeTxt = pov.sideLabel + ' ' + pov.angleDeg + '\u00b0';
            var bw = 100, bh = 28;
            ctx.fillStyle   = 'rgba(255,159,67,0.10)';
            roundRect(ctx, W - bw - 12, 12, bw, bh, 6);
            ctx.fill();
            ctx.strokeStyle = 'rgba(255,159,67,0.30)';
            ctx.lineWidth   = 1;
            roundRect(ctx, W - bw - 12, 12, bw, bh, 6);
            ctx.stroke();
            ctx.fillStyle   = '#FF9F43';
            ctx.font        = 'bold 12px Poppins, sans-serif';
            ctx.textAlign   = 'right';
            ctx.fillText(badgeTxt, W - 18, 31);
        }
    }

    // ── Animation loop ────────────────────────────────────────
    function animatePOV(pov) {
        if (animFrame) cancelAnimationFrame(animFrame);
        animTick = 0;
        function loop() {
            drawPOV(pov);
            animFrame = requestAnimationFrame(loop);
        }
        loop();
    }

    // ── Update stats panel ────────────────────────────────────
    function showSeatPOV(seatId) {
        var pov = getSeatPOV(seatId);
        if (!pov) return;

        seatLabel.textContent = 'Seat ' + pov.seatId + ' \u2014 Row ' + pov.row + ', Column ' + pov.col;
        statDistance.textContent = pov.distanceFt;
        statAngle.textContent    = pov.angleDeg === 0 ? 'Center' : pov.angleDeg + '\u00b0 ' + pov.sideLabel;
        statCoverage.textContent = pov.screenCoverage + '%';
        statQuality.textContent  = pov.qualityLabel;
        statQuality.style.color  = pov.qualityColor;

        qualityBar.style.width      = (pov.qualityScore / 5 * 100) + '%';
        qualityBar.style.background = pov.qualityColor;
        qualityBar.style.boxShadow  = '0 0 10px ' + pov.qualityColor + '60';

        animatePOV(pov);
    }

    // ── Open modal ────────────────────────────────────────────
    function openPOV() {
        if (typeof selectedSeats !== 'undefined' && selectedSeats.size > 0) {
            seatList = Array.from(selectedSeats);
        } else {
            seatList = Array.from(document.querySelectorAll('.seat.selected'))
                            .map(function(s){ return s.getAttribute('data-seat'); });
        }
        if (!seatList.length) return;

        currentSeatIndex = 0;
        if (seatList.length > 1) {
            seatNav.classList.add('visible');
        } else {
            seatNav.classList.remove('visible');
        }
        updateNavLabel();
        overlay.classList.add('active');
        showSeatPOV(seatList[0]);
    }

    // ── Close modal ───────────────────────────────────────────
    function closePOV() {
        overlay.classList.remove('active');
        if (animFrame) { cancelAnimationFrame(animFrame); animFrame = null; }
    }

    function updateNavLabel() {
        navLabel.textContent = 'Seat ' + (currentSeatIndex + 1) + ' of ' + seatList.length;
    }

    // ── Event listeners ───────────────────────────────────────

    document.addEventListener('click', function(e) {
        if (e.target.closest && e.target.closest('#pov-trigger-btn')) openPOV();
        else if (e.target.id === 'pov-trigger-btn') openPOV();
    });

    closeBtn.addEventListener('click', closePOV);

    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closePOV();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closePOV();
    });

    nextBtn.addEventListener('click', function() {
        currentSeatIndex = (currentSeatIndex + 1) % seatList.length;
        updateNavLabel();
        showSeatPOV(seatList[currentSeatIndex]);
    });

    prevBtn.addEventListener('click', function() {
        currentSeatIndex = (currentSeatIndex - 1 + seatList.length) % seatList.length;
        updateNavLabel();
        showSeatPOV(seatList[currentSeatIndex]);
    });

    // Keep trigger button enabled/disabled in sync with seat selection
    document.querySelectorAll('.seat:not(.booked)').forEach(function(seat) {
        seat.addEventListener('click', function() {
            setTimeout(function() {
                var povBtn = document.getElementById('pov-trigger-btn');
                if (povBtn) {
                    povBtn.disabled = document.querySelectorAll('.seat.selected').length === 0;
                }
            }, 10);
        });
    });

})();
</script>