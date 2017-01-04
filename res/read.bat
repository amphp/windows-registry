@echo off
setlocal ENABLEEXTENSIONS
set VALUE_NAME=DefaultColor
for /F "usebackq tokens=3" %%A IN (`reg query "%KEY_NAME%" /v "%VALUE_NAME%" 2^>nul ^| find "%VALUE_NAME%"`) do (
    echo %%A
)