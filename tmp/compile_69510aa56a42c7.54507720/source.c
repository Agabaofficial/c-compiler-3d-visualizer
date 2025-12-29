#include <stdio.h>

int main() {
    int a = 5;
    int b = 10;
    int sum = a + b;
    
    if (sum > 10) {
        printf("Sum is greater than 10: %d\n", sum);
    } else {
        printf("Sum is 10 or less: %d\n", sum);
    }
    
    for(int i = 0; i < 3; i++) {
        printf("Iteration %d\n", i);
    }
    
    return 0;
}