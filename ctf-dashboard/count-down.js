; (() => {
  const el = '#count-down'
  const countDown = document.querySelector(el)
  if (!countDown) return
  const untilTime = Number(countDown.dataset.until)
  if (!untilTime || untilTime < Date.now() / 1000) return
  countDown.innerHTML = `
<style>
@import url('https://fonts.googleapis.com/css?family=Roboto:100,300');

${el} { background-color:#f7f7f7;}
${el} .container {
  position: relative;
}

${el} .controlls {
  position: absolute;
  top: 105px;
  margin-left: auto;
  margin-right: auto;
  left: 0;
  right: 0;
  text-align: center;
}
${el} .circle {
  margin: 0 auto;
  width: 300px;
}

${el} .display-remain-time {
  font-family: 'Roboto';
  font-weight: 100;
  font-size: 65px;
  color: #F7958E;
}

${el} .e-c-base {
  fill: none;
  stroke: #B6B6B6;
  stroke-width: 4px
}

${el} .e-c-progress {
  fill: none;
  stroke: #F7958E;
  stroke-width: 4px;
  transition: stroke-dashoffset 0.7s;
}

${el} .e-c-pointer {
  fill: #FFF;
  stroke: #F7958E;
  stroke-width: 2px;
}

${el} #e-pointer { transition: transform 0.7s; }
</style>
<div class="container">
  <div class="circle"> <svg width="300" viewBox="0 0 220 220" xmlns="http://www.w3.org/2000/svg">
    <g transform="translate(110,110)">
      <circle r="100" class="e-c-base"/>
      <g transform="rotate(-90)">
        <circle r="100" class="e-c-progress"/>
        <g id="e-pointer">
          <circle cx="100" cy="0" r="8" class="e-c-pointer"/>
        </g>
      </g>
    </g>
    </svg> </div>
  <div class="controlls">
    <div class="display-remain-time">00:00</div>
  </div>
</div>
  `

  // CC: https://www.cssscript.com/demo/circular-countdown-timer-javascript-css3/

  //circle start
  const progressBar = document.querySelector('.e-c-progress');
  const pointer = document.getElementById('e-pointer');
  const length = Math.PI * 2 * 100;

  progressBar.style.strokeDasharray = length;

  function update(value, timePercent) {
    var offset = - length - length * value / (timePercent);
    progressBar.style.strokeDashoffset = offset;
    pointer.style.transform = `rotate(${360 * value / (timePercent)}deg)`;
  };

  //circle ends
  const displayOutput = document.querySelector('.display-remain-time')
  let intervalTimer;
  let timeLeft;
  const wholeTime = untilTime - Math.floor(Date.now() / 1000); // manage this to set the whole time 

  update(wholeTime, wholeTime); //refreshes progress bar
  displayTimeLeft(wholeTime);
  timer(wholeTime)

  function timer(seconds) { //counts time, takes seconds
    const remainTime = Date.now() + (seconds * 1000);
    displayTimeLeft(seconds);

    intervalTimer = setInterval(function () {
      timeLeft = Math.round((remainTime - Date.now()) / 1000);
      if (timeLeft < 0) {
        clearInterval(intervalTimer);
        // done count down
        location.reload()
        return;
      }
      displayTimeLeft(timeLeft);
    }, 1000);
  }
  function displayTimeLeft(timeLeft) { //displays time on the input
    let minutes = Math.floor(timeLeft / 60);
    let seconds = timeLeft % 60;
    let displayString = `${minutes < 10 ? '0' : ''}${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
    displayOutput.textContent = displayString;
    update(timeLeft, wholeTime);
  }
})()
